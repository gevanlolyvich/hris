<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FlexibleTime;
use App\Models\FlexibleTimeLog;
use App\Services\FlexibleTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlexibleTimeController extends Controller
{
    private FlexibleTimeService $service;

    public function __construct(FlexibleTimeService $service)
    {
        $this->service = $service;
    }

    private function guardMenu(): void
    {
        if (! FlexibleTime::canAccessMenu(Auth::user())) {
            abort(403, __('Permission denied.'));
        }
    }

    /**
     * List requests visible to the current user + pending approvals they can act on.
     */
    public function index()
    {
        $this->guardMenu();

        $user = Auth::user();
        $employee = $user->employee;

        $query = FlexibleTime::query()->with(['employee', 'approvedBy', 'rejectedBy', 'cancelledBy', 'createdBy']);

        if (! FlexibleTime::canViewAll($user) && $employee) {
            $subordinateIds = Employee::where('managed_by', $employee->id)->pluck('id')->toArray();
            $subordinateIds[] = $employee->id;

            $query->whereIn('employee_id', $subordinateIds);
        }

        $requests = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->limit(200)->get();

        $canCreate = $employee && $this->service->isEligible($employee);

        return view('flexibletime.index', compact('requests', 'canCreate'));
    }

    /**
     * Create form.
     */
    public function create()
    {
        $this->guardMenu();

        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee || ! $this->service->isEligible($employee)) {
            return redirect()->route('flexibletime.index')->with('error', __('You are not eligible for Flexible Time.'));
        }

        $today = date('Y-m-d');
        $normal = $this->service->getNormalWorkSchedule($employee, $today);

        return view('flexibletime.create', [
            'employee' => $employee,
            'normal' => $normal,
            'maxStart' => substr(FlexibleTime::MAX_START_TIME, 0, 5),
            'minDate' => $today,
        ]);
    }

    /**
     * Store a new pending request.
     */
    public function store(Request $request)
    {
        $this->guardMenu();

        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee || ! $this->service->isEligible($employee)) {
            return redirect()->route('flexibletime.index')->with('error', __('You are not eligible for Flexible Time.'));
        }

        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'requested_start_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $workDate = $data['date'];

        $error = $this->service->validateRequestedStart($employee, $workDate, $data['requested_start_time']);

        if ($error) {
            return redirect()->route('flexibletime.create')->with('error', $error)->withInput();
        }

        $exists = FlexibleTime::where('employee_id', $employee->id)
            ->where('date', $workDate)
            ->whereIn('status', [FlexibleTime::STATUS_PENDING, FlexibleTime::STATUS_APPROVED])
            ->exists();

        if ($exists) {
            return redirect()->route('flexibletime.create')->with('error', __('A Flexible Time request already exists for this date.'))->withInput();
        }

        $file = $request->file('document');
        $docName = time() . '_' . date('Y-m-d') . '_' . preg_replace('/\s+/', '', $employee->name) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/flexible-times', $docName, 'public');
        $documentPath = env('APP_URL') . '/storage/' . $path;

        $snapshot = $this->service->normalScheduleSnapshot($employee, $workDate);
        $requestedStart = $data['requested_start_time'] . ':00';

        DB::transaction(function () use ($data, $employee, $workDate, $snapshot, $requestedStart, $user, $documentPath) {
            $flexible = FlexibleTime::create([
                'employee_id' => $employee->id,
                'date' => $workDate,
                'normal_start_time' => $snapshot['normal_start_time'],
                'normal_end_time' => $snapshot['normal_end_time'],
                'requested_start_time' => $requestedStart,
                'requested_end_time' => $this->service->requestedEndFromStart($requestedStart),
                'reason' => $data['reason'],
                'document' => $documentPath,
                'status' => FlexibleTime::STATUS_PENDING,
                'created_by' => $user->id,
            ]);

            FlexibleTimeLog::create([
                'flexible_time_id' => $flexible->id,
                'from_status' => null,
                'to_status' => FlexibleTime::STATUS_PENDING,
                'changed_by' => $user->id,
                'note' => 'Request created',
                'created_at' => now(),
            ]);
        });

        return redirect()->route('flexibletime.index')->with('success', __('Flexible Time request submitted.'));
    }

    /**
     * Detail page.
     */
    public function show($id)
    {
        $this->guardMenu();

        $flexible = FlexibleTime::with(['employee', 'approvedBy', 'rejectedBy', 'cancelledBy', 'createdBy', 'logs.changedBy'])
            ->findOrFail($id);

        if (! FlexibleTime::canView(Auth::user(), $flexible)) {
            abort(403, __('Permission denied.'));
        }

        $canApprove = FlexibleTime::canApprove(Auth::user(), $flexible) && $flexible->isPending();
        $canCancel = FlexibleTime::canCancel(Auth::user(), $flexible);

        return view('flexibletime.show', compact('flexible', 'canApprove', 'canCancel'));
    }

    /**
     * Approve: effective schedule changes only for this date.
     */
    public function approve($id)
    {
        $flexible = FlexibleTime::findOrFail($id);

        if (! FlexibleTime::canApprove(Auth::user(), $flexible)) {
            abort(403, __('Permission denied.'));
        }

        if (! $flexible->isPending()) {
            return redirect()->back()->with('error', __('This request has already been processed.'));
        }

        DB::transaction(function () use ($flexible) {
            $flexible->status = FlexibleTime::STATUS_APPROVED;
            $flexible->approved_start_time = $flexible->requested_start_time;
            $flexible->approved_end_time = $flexible->requested_end_time;
            $flexible->approved_by = Auth::id();
            $flexible->approved_at = now();
            $flexible->save();

            FlexibleTimeLog::create([
                'flexible_time_id' => $flexible->id,
                'from_status' => FlexibleTime::STATUS_PENDING,
                'to_status' => FlexibleTime::STATUS_APPROVED,
                'changed_by' => Auth::id(),
                'note' => 'Approved',
                'created_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', __('Flexible Time request approved.'));
    }

    /**
     * Reject with optional reason.
     */
    public function reject(Request $request, $id)
    {
        $flexible = FlexibleTime::findOrFail($id);

        if (! FlexibleTime::canApprove(Auth::user(), $flexible)) {
            abort(403, __('Permission denied.'));
        }

        if (! $flexible->isPending()) {
            return redirect()->back()->with('error', __('This request has already been processed.'));
        }

        $data = $request->validate([
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($flexible, $data) {
            $flexible->status = FlexibleTime::STATUS_REJECTED;
            $flexible->rejected_by = Auth::id();
            $flexible->rejected_at = now();
            $flexible->rejection_reason = $data['rejection_reason'] ?? null;
            $flexible->save();

            FlexibleTimeLog::create([
                'flexible_time_id' => $flexible->id,
                'from_status' => FlexibleTime::STATUS_PENDING,
                'to_status' => FlexibleTime::STATUS_REJECTED,
                'changed_by' => Auth::id(),
                'note' => $data['rejection_reason'] ?? 'Rejected',
                'created_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', __('Flexible Time request rejected.'));
    }

    /**
     * Cancel own pending request.
     */
    public function cancel($id)
    {
        $flexible = FlexibleTime::findOrFail($id);

        if (! FlexibleTime::canCancel(Auth::user(), $flexible)) {
            abort(403, __('Permission denied.'));
        }

        DB::transaction(function () use ($flexible) {
            $from = $flexible->status;
            $flexible->status = FlexibleTime::STATUS_CANCELLED;
            $flexible->cancelled_by = Auth::id();
            $flexible->cancelled_at = now();
            $flexible->save();

            FlexibleTimeLog::create([
                'flexible_time_id' => $flexible->id,
                'from_status' => $from,
                'to_status' => FlexibleTime::STATUS_CANCELLED,
                'changed_by' => Auth::id(),
                'note' => 'Cancelled by requester',
                'created_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', __('Flexible Time request cancelled.'));
    }
}

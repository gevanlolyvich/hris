<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Permit;
use App\Models\PermitType;
use App\Models\PushSubscription;
use App\Models\ShiftTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PermitController extends Controller
{
    public function index(Request $request)
    {
        $branch = Branch::find(\Auth::user()->branch_id);
        $branch_id = collect();
        if ($branch) {
            $branch_id->push($branch?->id);
        }

        $children = $branch?->childBranchFlatten();
        if ($children?->isNotEmpty()) {
            foreach ($children as $child) {
                $branch_id->push($child->id);
            }
        }

        $status = $request->query('status', null);
        $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
        $department = collect();

        if (\Auth::user()->can('Manage Leave')) {
            if (Auth::user()->type == 'employee') {
                $subordinates = \Auth::user()->employee->subordinatesFlatten();
                $employees = collect();

                // Check if employee managing other employee or not
                if ($subordinates->isNotEmpty()) {
                    foreach ($subordinates as $subordinate) {
                        $employees->push($subordinate->id);
                    }
                }
                $employees->push(\Auth::user()->employee->id);

                $permits   = Permit::whereIn('employee_id', $employees)->orderBy('start_date', 'DESC');
            } else {
                $permits = $branch_id?->isNotEmpty() ? Permit::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->orderBy('start_date', 'DESC') : Permit::orderBy('start_date', 'DESC');
            }

            if ($status != null && $status == 'Pending') {
                $permits->where('status', 'Pending');
            }

            if (!empty($request->branch_id)) {
                $department     = Department::where('branch_id', $request->branch_id)->get()->pluck('name', 'id');
                $permits        = $permits->whereHas('employee', function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                });
            }
            if (!empty($request->department_id)) {
                $department     = empty($request->branch_id) ? Department::where('department_id', $request->department_id)->get()->pluck('name', 'id') : $department;
                $permits        = $permits->whereHas('employee', function ($query) use ($request) {
                    $query->where('department_id', $request->department_id);
                });
            }

            $permits = $permits->get();

            $branch_count = 2;
            foreach ($branch as $index => $b) {
                if ($b == 'Head Office') {
                    $branch[$index] = '1. ' .  $b;
                } else {
                    $branch[$index] = $branch_count . '. ' . __($b);
                    $branch_count += 1;
                }
            }

            return view('permit.index', compact('permits', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('is_active', 1)->where('user_id', '=', Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
            } else {
                $branch = Branch::find(\Auth::user()->branch_id);
                $branch_id = collect();
                if ($branch) {
                    $branch_id->push($branch?->id);
                }

                $children = $branch?->childBranchFlatten();
                if ($children?->isNotEmpty()) {
                    foreach ($children as $child) {
                        $branch_id->push($child->id);
                    }
                }

                $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            }
            $permittypes   = PermitType::get();

            return view('permit.create', compact('employees', 'permittypes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function shiftDates(Request $request)
    {
        $employee = Employee::where('is_active', 1)->find($request->get('employee_id'));

        if (!$employee || $employee->is_shift != 1) {
            return response()->json(['unrestricted' => true]);
        }

        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->get('month', date('Y-m')))
            ? $request->get('month')
            : date('Y-m');

        $schedule = $employee->monthlyShiftSchedule(date('m', strtotime($month)), date('Y', strtotime($month)));

        return response()->json([
            'dates' => array_values(array_keys($schedule->toArray())),
        ]);
    }

    private function validateShiftDates(Employee $employee, $startDate, $endDate)
    {
        if ($employee->is_shift != 1) {
            return true;
        }

        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            new \DateTime(date('Y-m-d', strtotime('+1 day', strtotime($endDate))))
        );

        foreach ($period as $value) {
            if (!$employee->hasScheduledShiftOn($value->format('Y-m-d'))) {
                return false;
            }
        }

        return true;
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Leave')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'permit_type_id'    => 'required',
                    'start_date'        => 'required',
                    'end_date'          => 'required',
                    'reason'            => 'required',
                    'myDocument'        => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $start_date = date($request->start_date);
            $end_date   = date($request->end_date);
            $total_permit_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
            $permit_type = PermitType::find($request->permit_type_id);

            $permit = new Permit();

            if (Auth::user()->type == "employee") {
                $permit->employee_id = $employee->id;
            } else {
                $permit->employee_id = $request->employee_id;
            }

            $employee = Employee::where('is_active', 1)->find($permit->employee_id);

            if (empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }

            if (!$this->validateShiftDates($employee, $request->start_date, $request->end_date)) {
                return redirect()->back()->with('error', __('Selected date has no scheduled shift for this employee'));
            }

            $duplicate_permit = Permit::where('employee_id', $permit->employee_id)
                ->where(function ($query) use ($start_date, $end_date) {
                    $query->whereBetween('start_date', [$start_date, $end_date])
                        ->orWhereBetween('end_date', [$start_date, $end_date])
                        ->orWhere(function ($query) use ($start_date, $end_date) {
                            $query->where('start_date', '<=', $start_date)
                                ->where('end_date', '>=', $end_date);
                        });
                })
                ->first();

            if (!empty($duplicate_permit)) {
                return redirect()->back()->with('error', __('Permit Already Exist In That Date Range'));
            }

            $document_path = null;
            if ($request->file('myDocument')) {
                $docs = $request->file('myDocument');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/permits', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }

            $permit->permit_type_id     = $permit_type->id;
            $permit->start_date         = $request->start_date;
            $permit->end_date           = $request->end_date;
            $permit->total_permit_days  = $total_permit_days + 1;
            $permit->reason             = $request->reason;
            $permit->docs               = $document_path;
            $permit->status             = 'Pending';
            $permit->created_by         = Auth::user()->id;

            $permit->save();

            // Send Notification To HR
            $subscriptions      = [];
            $branch             = $employee->branch_id ?? '';
            $departement        = $employee->department_id ?? '';
            $pushSubscriptions  = PushSubscription::whereHas('user', function ($query) use ($employee) {
                $query->where('type', 'hr')
                    ->where(function ($query) use ($employee) {
                        $query->whereNull('branch_id')
                            ->orWhere('branch_id', $employee->branch_id);
                    });
            })->get();
            foreach ($pushSubscriptions as $sub) {
                array_push($subscriptions, ['data' => $sub->data, 'name' => $sub->user->name]);
            }
            \Auth::user()->sendNotifications(
                $subscriptions,
                json_encode([
                    'title' => __('New Permit Request'),
                    'body' => $employee->name . ' ' . __('Make Permit Request') . ' [' .  $permit->permitType->name . '] ' . __('For Date') . ' ' . $request->start_date . ' ' . __('To Date') . ' ' . $request->end_date,
                    'url' => "/permit?branch_id={$branch}&department_id={$departement}"
                ]),
                'high'
            );

            return redirect()->back()->with('success', __('Attendance Permit Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Permit $permit)
    {
        return redirect()->back();
    }

    public function edit($id)
    {
        $permit = Permit::find($id);

        if (\Auth::user()->can('Edit Leave')) {
            if ($permit->created_by == Auth::user()->id || $permit->employee_id == Auth::user()?->employee?->id || \Auth::user()->type != 'employee') {

                $branch = Branch::find(\Auth::user()->branch_id);
                $branch_id = collect();
                if ($branch) {
                    $branch_id->push($branch?->id);
                }


                $children = $branch?->childBranchFlatten();
                if ($children?->isNotEmpty()) {
                    foreach ($children as $child) {
                        $branch_id->push($child->id);
                    }
                }

                $employees  = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->orderby('name', 'asc')->get()->pluck('name', 'id');
                $permittype = PermitType::get()->pluck('name', 'id');

                return view('permit.edit', compact('permit', 'employees', 'permittype'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $permit_id)
    {
        $permit = Permit::find($permit_id);
        if (\Auth::user()->can('Edit Leave')) {
            if ($permit->created_by == Auth::user()->id || $permit->employee_id == Auth::user()?->employee?->id || \Auth::user()->type != 'employee') {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'permit_type_id'    => 'required',
                        'start_date'        => 'required',
                        'end_date'          => 'required',
                        'reason'            => 'required',
                        'myDocument'        => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                // return $request;

                //* Custom Form
                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $start_date = date($request->start_date);
                $end_date   = date($request->end_date);
                $total_permit_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

                $duplicate_permit = Permit::whereNot('id', $permit->id)->where('employee_id', $permit->employee_id)
                    ->where(function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('start_date', [$start_date, $end_date])
                            ->orWhereBetween('end_date', [$start_date, $end_date])
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('start_date', '<=', $start_date)
                                    ->where('end_date', '>=', $end_date);
                            });
                    })
                    ->first();

                if (!empty($duplicate_permit)) {
                    return redirect()->back()->with('error', __('Permit Already Exist In That Date Range'));
                }

                if ($permit->employee && !$this->validateShiftDates($permit->employee, $request->start_date, $request->end_date)) {
                    return redirect()->back()->with('error', __('Selected date has no scheduled shift for this employee'));
                }

                $date = date_create($request->date);
                $document_path = null;
                if ($request->file('myDocument')) {
                    $docs = $request->file('myDocument');
                    $docName = time() . "_" . date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $permit->employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/permits', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                //* Input Data
                $form = [
                    'employee_id'       => $request->employee_id,
                    'start_date'        => $request->start_date,
                    'end_date'          => $request->end_date,
                    'total_permit_days' => $total_permit_days + 1,
                    'reason'            => $request->reason,
                    'docs'              => $document_path ? $document_path : $permit->docs,
                ];

                //* Update Data
                Permit::where('id', $permit->id)->update($form);
                return redirect()->back()->with('success', __('Attendance Permit Successfully Updated'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Permit $permit)
    {
        if (\Auth::user()->can('Delete Leave')) {
            if ($permit->created_by == Auth::user()->id || $permit->employee_id == Auth::user()?->employee?->id || \Auth::user()->type != 'employee') {

                if ($permit->docs) {
                    $filepath_array = explode('/', $permit->docs);
                    $filename = array_pop($filepath_array);

                    // Check if the file exists before attempting to delete
                    if (Storage::disk('public')->exists("uploads/permits/$filename")) {
                        Storage::disk('public')->delete("uploads/permits/$filename");
                    }
                }

                if ($permit->status == "Approved") {
                    $dates = [];
                    $period = new \DatePeriod(
                        new \DateTime($permit->start_date),
                        new \DateInterval('P1D'),
                        new \DateTime(date('Y-m-d', strtotime('+1 day', strtotime($permit->end_date))))
                    );

                    foreach ($period as $key => $value) {
                        array_push($dates, $value->format('Y-m-d'));
                    }

                    AttendanceEmployee::where('employee_id', $permit->employee_id)->whereIn('date', $dates)->where('status', 'Permission')->delete();
                }

                $permit->delete();
                return redirect()->back()->with('success', __('Attendance Permit Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        $permit         = Permit::find($id);
        $employee       = Employee::find($permit->employee_id);

        return view('permit.action', compact('employee', 'permit'));
    }

    public function changeaction(Request $request)
    {
        $permit = Permit::find($request->permit_id);

        $form = [
            'status'        => $request->status,
            'is_approved'   => $request->status == 'Approved',
            'approved_by'   => Auth::user()->id,
        ];

        if ($request->status == 'Approved') {
            $dates = [];

            if ($permit->start_date == $permit->end_date) {
                array_push($dates, $permit->start_date);
            } else {
                $period = new \DatePeriod(
                    new \DateTime($permit->start_date),
                    new \DateInterval('P1D'),
                    new \DateTime(date('Y-m-d', strtotime('+1 day', strtotime($permit->end_date))))
                );

                foreach ($period as $key => $value) {
                    array_push($dates, $value->format('Y-m-d'));
                }
            }

            $permitAttendance = AttendanceStatus::find(3);

            $createPermissionRow = function ($employeeId, $date, $shiftTypeId) use ($permitAttendance) {
                AttendanceEmployee::create([
                    'employee_id'           => $employeeId,
                    'date'                  => $date,
                    'attendance_status_id'  => $permitAttendance->id,
                    'status'                => $permitAttendance->name,
                    'clock_in'              => '00:00:00',
                    'clock_out'             => '00:00:01',
                    'late'                  => '00:00:00',
                    'early_leaving'         => '00:00:00',
                    'work_hours'            => '00:00:00',
                    'overtime'              => '00:00:00',
                    'total_rest'            => '00:00:00',
                    'created_by'            => $employeeId,
                    'attendance_type_id'    => null, //* ON SITE
                    'coord_in'              => null,
                    'coord_out'             => null,
                    'is_valid'              => true,
                    'validate_by'           => Auth::user()->id,
                    'shift_type_id'         => $shiftTypeId,
                    'source_in'             => 'Application',
                    'source_out'            => 'Application'
                ]);
            };

            $hasAttendanceOn = function ($employeeId, $date, $shiftTypeId = null) {
                $query = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $date);
                if (!empty($shiftTypeId)) {
                    $query->where('shift_type_id', $shiftTypeId);
                }
                return $query->exists();
            };

            for ($i = 0; $i < count($dates); $i++) {
                $date = $dates[$i];

                if ($permit->employee->is_shift) {
                    // Shift employee: satu baris Permission untuk tiap shift terjadwal
                    // yang belum memiliki record attendance (hadir/Permission/dll).
                    $shiftTypeIds = $permit->employee->scheduledShiftIdsForDate($date);

                    if (empty($shiftTypeIds)) {
                        $shiftTypeIds = [$permit->employee->shift_type_id];
                    }

                    foreach ($shiftTypeIds as $shiftTypeId) {
                        if (!empty($shiftTypeId) && !$hasAttendanceOn($permit->employee_id, $date, $shiftTypeId)) {
                            $createPermissionRow($permit->employee_id, $date, $shiftTypeId);
                        }
                    }
                } else {
                    // Non-shift: satu baris Permission per tanggal (perilaku sebelumnya).
                    if (!$hasAttendanceOn($permit->employee_id, $date)) {
                        $createPermissionRow($permit->employee_id, $date, $permit->employee->shift_type_id);
                    }
                }
            }
        }

        DB::transaction(function () use ($permit, $form) {
            Permit::where('id', $permit->id)->update($form);
            // AttendanceEmployee::create($form_attendance);
        });

        // Send push notification to requester
        $subscriptions = [];
        if ($permit->employee?->user?->pushNotifications) {
            foreach ($permit->employee->user->pushNotifications ?? [] as $sub) {
                array_push($subscriptions, ['data' => $sub->data, 'name' => $permit->employee->name]);
            }
        }
        $status = $request->status == 'Approved' ? 'Approved' : 'Rejected';
        \Auth::user()->sendNotifications(
            $subscriptions,
            json_encode([
                'title' => __('Permit Request') . ' ' . __($status),
                'body' => __('Permit Request') . ' [' . $permit->permitType->name . '] ' . __('For Date') . ' ' . $permit->start_date . ' ' . __('To Date') . ' ' . $permit->end_date . ' ' . __($status),
                'url' => "/permit"
            ]),
            'high'
        );

        return redirect()->back()->with('success', __('Attendance Permit Successfully Updated'));
    }
}

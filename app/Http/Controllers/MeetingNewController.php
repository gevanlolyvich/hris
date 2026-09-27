<?php

namespace App\Http\Controllers;

use App\Models\MeetingNew;
use App\Models\MeetingAttendee;
use App\Models\MeetingResult;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Branch;
use App\Models\Utility;
use App\Exports\MeetingResultExport;
use App\Exports\XlsxMultiLinkPostProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class MeetingNewController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Meeting New')) {
            $query = MeetingNew::with('attendees.employee');

            if (\Auth::user()->type != 'company') {
                $query->where('created_by', \Auth::user()->id);
            }

            $meetings = $query->orderBy('id', 'desc')->get();

            return view('meetingNew.index', compact('meetings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Meeting New')) {
            $branches = Branch::orderby('name', 'asc')->get();

            $departments = Department::orderby('name', 'asc')->get();
            $employees = Employee::where('is_active', 1)->orderby('name', 'asc')->get();

            return view('meetingNew.create', compact('employees', 'departments', 'branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Meeting New')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'meeting_type' => 'required',
                    'meeting_date' => 'required|date',
                    'meeting_time' => 'required',
                    'employee_id' => 'required|array|min:1',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $employeeIds = collect($request->employee_id);
            $employees = Employee::whereIn('id', $employeeIds)->get();
            $deptIds = $employees->pluck('department_id');

            if ($deptIds->count() !== $deptIds->unique()->count()) {
                return redirect()->back()->with('error', __('Maksimal 1 employee per department.'));
            }

            $meeting = new MeetingNew();
            $meeting->title = $request->title;
            $meeting->meeting_type = $request->meeting_type;
            $meeting->meeting_date = $request->meeting_date;
            $meeting->meeting_time = $request->meeting_time;
            $meeting->deadline = Carbon::parse($request->meeting_date . ' ' . $request->meeting_time)->addWeek();
            $meeting->created_by = \Auth::user()->id;

            if ($request->hasFile('document')) {
                $documents = [];
                foreach ($request->file('document') as $key => $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $dir = 'app/public/uploads/meetingNew/';
                    $path = \Utility::upload_coustom_file($request, 'document', $filename, $dir, $key, []);
                    if ($path['flag'] == 1) {
                        $documents[] = $filename;
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }
                $meeting->document = $documents;
            }

            $meeting->save();

            foreach ($request->employee_id as $empId) {
                MeetingAttendee::create([
                    'meeting_new_id' => $meeting->id,
                    'employee_id' => $empId,
                ]);

                MeetingResult::create([
                    'meeting_new_id' => $meeting->id,
                    'employee_id' => $empId,
                ]);
            }

            $attendees = Employee::whereIn('id', $request->employee_id)->get();
            foreach ($attendees as $attendee) {
                if ($attendee->user) {
                    try {
                        Mail::to($attendee->user->email)->send(
                            new \App\Mail\MeetingNewNotification($meeting, $attendee)
                        );
                    } catch (\Exception $e) {
                        \Log::error('Failed to send meeting notification email: ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('meeting-new.index')->with('success', __('Meeting successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        $meeting = MeetingNew::with('attendees.employee', 'results.employee')->findOrFail($id);

        $attendeeEmployeeIds = $meeting->attendees->pluck('employee_id')->toArray();
        $attendeeDeptIds = Employee::whereIn('id', $attendeeEmployeeIds)->pluck('department_id')->unique()->toArray();

        $currentEmployee = Employee::where('user_id', Auth::id())->first();
        $accessibleDeptIds = [11, 12, 16];

        $canView = false;
        if (\Auth::user()->type == 'company' || !$currentEmployee) {
            $canView = true;
        } elseif (in_array($currentEmployee->department_id, $accessibleDeptIds)) {
            $canView = true;
        } elseif (in_array($currentEmployee->department_id, $attendeeDeptIds)) {
            $canView = true;
        } elseif (in_array($currentEmployee->id, $attendeeEmployeeIds)) {
            $canView = true;
        }

        if (!$canView) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $documentUrls = [];
        if ($meeting->document && is_array($meeting->document)) {
            foreach ($meeting->document as $doc) {
                $documentUrls[] = \Utility::get_file('uploads/meetingNew') . '/' . $doc;
            }
        }

        return view('meetingNew.show', compact('meeting', 'documentUrls'));
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit Meeting New')) {
            $meeting = MeetingNew::with('attendees')->findOrFail($id);

            if ($meeting->created_by != Auth::id() || $meeting->isLocked()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $branches = Branch::orderby('name', 'asc')->get();

            $employees = Employee::where('is_active', 1)->orderby('name', 'asc')->get();
            $departments = Department::orderby('name', 'asc')->get();

            $selectedEmployeeIds = $meeting->attendees->pluck('employee_id')->toArray();

            return view('meetingNew.edit', compact('meeting', 'employees', 'departments', 'selectedEmployeeIds', 'branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('Edit Meeting New')) {
            $meeting = MeetingNew::findOrFail($id);

            if ($meeting->created_by != Auth::id() || $meeting->isLocked()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'meeting_type' => 'required',
                    'meeting_date' => 'required|date',
                    'meeting_time' => 'required',
                    'employee_id' => 'required|array|min:1',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $employeeIds = collect($request->employee_id);
            $employees = Employee::whereIn('id', $employeeIds)->get();
            $deptIds = $employees->pluck('department_id');

            if ($deptIds->count() !== $deptIds->unique()->count()) {
                return redirect()->back()->with('error', __('Maksimal 1 employee per department.'));
            }

            $meeting->title = $request->title;
            $meeting->meeting_type = $request->meeting_type;
            $meeting->meeting_date = $request->meeting_date;
            $meeting->meeting_time = $request->meeting_time;
            $meeting->deadline = Carbon::parse($request->meeting_date . ' ' . $request->meeting_time)->addWeek();

            $docs = $meeting->document && is_array($meeting->document) ? $meeting->document : [];

            if ($request->has('delete_documents')) {
                foreach ($request->delete_documents as $delDoc) {
                    if (($key = array_search($delDoc, $docs)) !== false) {
                        unset($docs[$key]);
                        $path = storage_path('app/public/uploads/meetingNew/' . $delDoc);
                        if (\File::exists($path)) {
                            \File::delete($path);
                        }
                    }
                }
                $docs = array_values($docs);
                $meeting->document = $docs;
            }

            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $key => $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $dir = 'app/public/uploads/meetingNew/';
                    $path = \Utility::upload_coustom_file($request, 'document', $filename, $dir, $key, []);
                    if ($path['flag'] == 1) {
                        $docs[] = $filename;
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }

                $meeting->document = $docs;
            }

            $meeting->save();

            $oldAttendeeIds = $meeting->attendees->pluck('employee_id')->toArray();
            $newAttendeeIds = $request->employee_id;

            $removedIds = array_diff($oldAttendeeIds, $newAttendeeIds);
            $addedIds = array_diff($newAttendeeIds, $oldAttendeeIds);

            MeetingAttendee::where('meeting_new_id', $meeting->id)
                ->whereIn('employee_id', $removedIds)->delete();

            MeetingResult::where('meeting_new_id', $meeting->id)
                ->whereIn('employee_id', $removedIds)->delete();

            foreach ($addedIds as $empId) {
                MeetingAttendee::create([
                    'meeting_new_id' => $meeting->id,
                    'employee_id' => $empId,
                ]);

                MeetingResult::create([
                    'meeting_new_id' => $meeting->id,
                    'employee_id' => $empId,
                ]);

                $attendee = Employee::find($empId);
                if ($attendee && $attendee->user) {
                    try {
                        Mail::to($attendee->user->email)->send(
                            new \App\Mail\MeetingNewNotification($meeting, $attendee)
                        );
                    } catch (\Exception $e) {
                        \Log::error('Failed to send meeting notification email: ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('meeting-new.index')->with('success', __('Meeting successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('Delete Meeting New')) {
            $meeting = MeetingNew::findOrFail($id);

            if ($meeting->isDeleteLocked()) {
                return redirect()->back()->with('error', __('Meeting sudah lewat 1 minggu, tidak bisa dihapus.'));
            }

            if (\Auth::user()->type != 'company' && $meeting->created_by != Auth::id()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            if ($meeting->document && is_array($meeting->document)) {
                foreach ($meeting->document as $doc) {
                    $path = storage_path('app/public/uploads/meetingNew/' . $doc);
                    if (\File::exists($path)) {
                        \File::delete($path);
                    }
                }
            }

            MeetingAttendee::where('meeting_new_id', $meeting->id)->delete();
            MeetingResult::where('meeting_new_id', $meeting->id)->delete();
            $meeting->delete();

            return redirect()->back()->with('success', __('Meeting successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function resultIndex()
    {
        $currentEmployee = Employee::where('user_id', Auth::id())->first();
        $accessibleDeptIds = [11, 12, 16];

        if (\Auth::user()->type == 'company' || !$currentEmployee) {
            $meetings = MeetingNew::with('attendees.employee', 'results.employee')
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $attendeeMeetingIds = MeetingAttendee::where('employee_id', $currentEmployee->id)
                ->pluck('meeting_new_id')->toArray();

            $deptMeetingIds = MeetingAttendee::whereHas('employee', function ($q) use ($currentEmployee) {
                $q->where('department_id', $currentEmployee->department_id);
            })->pluck('meeting_new_id')->toArray();

            if (in_array($currentEmployee->department_id, $accessibleDeptIds)) {
                $meetings = MeetingNew::with('attendees.employee', 'results.employee')
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                $meetingIds = array_unique(array_merge($attendeeMeetingIds, $deptMeetingIds));
                $meetings = MeetingNew::with('attendees.employee', 'results.employee')
                    ->whereIn('id', $meetingIds)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        $resultRows = collect();

        $isPrivileged = \Auth::user()->type == 'company'
            || !$currentEmployee
            || in_array($currentEmployee->department_id, $accessibleDeptIds);

        foreach ($meetings as $meeting) {
            if ($isPrivileged) {
                foreach ($meeting->attendees as $attendee) {
                    $res = $meeting->results
                        ->where('employee_id', $attendee->employee_id)
                        ->whereNotNull('filled_at')
                        ->first();

                    $resultRows->push((object) [
                        'meeting' => $meeting,
                        'result' => $res,
                        'employee' => $res ? $res->employee : $attendee->employee,
                        'is_my_result' => $currentEmployee && $currentEmployee->id == $attendee->employee_id,
                        'is_filled' => (bool) $res,
                    ]);
                }
            } else {
                $isAttendee = $meeting->attendees->contains('employee_id', $currentEmployee->id);
                $deptEmployeeIds = Employee::where('department_id', $currentEmployee->department_id)
                    ->pluck('id');

                foreach ($meeting->results->whereNotNull('filled_at') as $res) {
                    if ($deptEmployeeIds->contains($res->employee_id)) {
                        $resultRows->push((object) [
                            'meeting' => $meeting,
                            'result' => $res,
                            'employee' => $res->employee,
                            'is_my_result' => $currentEmployee->id == $res->employee_id,
                            'is_filled' => true,
                        ]);
                    }
                }

                $myFilled = $meeting->results
                    ->where('employee_id', $currentEmployee->id)
                    ->whereNotNull('filled_at')
                    ->first();

                if ($isAttendee && !$myFilled) {
                    $resultRows->push((object) [
                        'meeting' => $meeting,
                        'result' => null,
                        'employee' => $currentEmployee,
                        'is_my_result' => true,
                        'is_filled' => false,
                    ]);
                }
            }
        }

        return view('meetingNew.resultIndex', compact('resultRows'));
    }

    public function resultExport()
    {
        $currentEmployee = Employee::where('user_id', Auth::id())->first();
        $accessibleDeptIds = [11, 12, 16];

        if (\Auth::user()->type != 'company'
            && (!$currentEmployee || !in_array($currentEmployee->department_id, $accessibleDeptIds))) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $export = new MeetingResultExport();
        $name = 'hasil_meeting_' . date('Ymd_His');
        $fileName = $name . '.xlsx';

        $disk = \Storage::disk('public');
        $dir = 'exports';
        $disk->makeDirectory($dir);

        Excel::store($export, $dir . '/' . $fileName, 'public');

        $filePath = $disk->path($dir . '/' . $fileName);
        XlsxMultiLinkPostProcessor::embed($filePath, $export->docCells);

        return \Response::download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function resultShow(Request $request, $id)
    {
        $meeting = MeetingNew::with('attendees.employee', 'results.employee')->findOrFail($id);

        $currentEmployee = Employee::where('user_id', Auth::id())->first();
        $accessibleDeptIds = [11, 12, 16];

        $attendeeEmployeeIds = $meeting->attendees->pluck('employee_id')->toArray();
        $attendeeDeptIds = Employee::whereIn('id', $attendeeEmployeeIds)->pluck('department_id')->unique()->toArray();

        $canView = false;
        if (\Auth::user()->type == 'company' || !$currentEmployee) {
            $canView = true;
        } elseif (in_array($currentEmployee->department_id, $accessibleDeptIds)) {
            $canView = true;
        } elseif (in_array($currentEmployee->department_id, $attendeeDeptIds)) {
            $canView = true;
        } elseif (in_array($currentEmployee->id, $attendeeEmployeeIds)) {
            $canView = true;
        }

        if (!$canView) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $documentUrls = [];
        if ($meeting->document && is_array($meeting->document)) {
            foreach ($meeting->document as $doc) {
                $documentUrls[] = \Utility::get_file('uploads/meetingNew') . '/' . $doc;
            }
        }

        $employeeId = $request->query('employee_id');

        if (\Auth::user()->type == 'company' || !$currentEmployee
            || in_array($currentEmployee->department_id, $accessibleDeptIds)) {
            if ($employeeId) {
                $target = $meeting->results
                    ->where('employee_id', $employeeId)
                    ->whereNotNull('filled_at')
                    ->first();
                $visibleResults = $target ? collect([$target]) : collect();
            } else {
                $visibleResults = $meeting->results->whereNotNull('filled_at');
            }
        } else {
            $deptEmployeeIds = Employee::where('department_id', $currentEmployee->department_id)
                ->pluck('id');
            if ($employeeId) {
                $target = $meeting->results
                    ->where('employee_id', $employeeId)
                    ->whereNotNull('filled_at')
                    ->first();
                $visibleResults = ($target && $deptEmployeeIds->contains($target->employee_id))
                    ? collect([$target])
                    : collect();
            } else {
                $visibleResults = $meeting->results->filter(function ($res) use ($deptEmployeeIds) {
                    return $res->filled_at && $deptEmployeeIds->contains($res->employee_id);
                });
            }
        }

        return view('meetingNew.resultShow', compact('meeting', 'documentUrls', 'visibleResults'));
    }

    public function resultFill($id)
    {
        $meeting = MeetingNew::with('attendees.employee', 'results')->findOrFail($id);

        $currentEmployee = Employee::where('user_id', Auth::id())->first();

        if (!$currentEmployee) {
            return redirect()->back()->with('error', __('Anda tidak dapat mengisi hasil meeting ini.'));
        }

        $isAttendee = $meeting->attendees->contains('employee_id', $currentEmployee->id);

        if (!$isAttendee || $meeting->isLocked()) {
            return redirect()->back()->with('error', __('Anda tidak dapat mengisi hasil meeting ini.'));
        }

        $result = MeetingResult::where('meeting_new_id', $meeting->id)
            ->where('employee_id', $currentEmployee->id)
            ->first();

        $documentUrls = [];
        if ($meeting->document && is_array($meeting->document)) {
            foreach ($meeting->document as $doc) {
                $documentUrls[] = \Utility::get_file('uploads/meetingNew') . '/' . $doc;
            }
        }

        return view('meetingNew.fill', compact('meeting', 'result', 'documentUrls'));
    }

    public function resultStore(Request $request, $id)
    {
        $meeting = MeetingNew::findOrFail($id);

        if ($meeting->isLocked()) {
            return redirect()->back()->with('error', __('Meeting sudah ter-lock, tidak bisa diisi lagi.'));
        }

        $currentEmployee = Employee::where('user_id', Auth::id())->first();

        if (!$currentEmployee) {
            return redirect()->back()->with('error', __('Anda tidak terdaftar sebagai peserta meeting ini.'));
        }

        $isAttendee = $meeting->attendees->contains('employee_id', $currentEmployee->id);

        if (!$isAttendee) {
            return redirect()->back()->with('error', __('Anda tidak terdaftar sebagai peserta meeting ini.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'content' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        MeetingResult::where('meeting_new_id', $meeting->id)
            ->where('employee_id', $currentEmployee->id)
            ->update([
                'content' => $request->content,
                'filled_at' => Carbon::now(),
            ]);

        return redirect()->route('meeting-result.index')->with('success', __('Hasil meeting berhasil disimpan.'));
    }

    public function getemployee(Request $request)
    {
        $query = Employee::where('is_active', 1);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->department_id) {
            $query->whereIn('department_id', $request->department_id);
        }

        $employees = $query->orderby('name', 'asc')
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'department_id' => $emp->department_id,
                    'department_name' => $emp->department ? $emp->department->name : '-',
                ];
            });

        return response()->json($employees);
    }

    public function getdepartment(Request $request)
    {
        if ($request->branch_id) {
            $departments = Department::where('branch_id', $request->branch_id)
                ->orderby('name', 'asc')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        } else {
            $departments = Department::orderby('name', 'asc')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }

        return response()->json($departments);
    }
}

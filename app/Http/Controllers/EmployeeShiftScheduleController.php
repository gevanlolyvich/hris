<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShiftSchedule;
use App\Models\ShiftType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeShiftScheduleController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Shift')) {
            $branch_id = $this->allowedBranchIds();

            $employees = $branch_id?->isNotEmpty()
                ? Employee::where('is_active', 1)->where('is_shift', 1)->whereIn('branch_id', $branch_id)->orderBy('name', 'asc')->get()
                : Employee::where('is_active', 1)->where('is_shift', 1)->orderBy('name', 'asc')->get();

            $month = $request->get('month', date('Y-m'));

            // Past months are locked; requests for them are clamped to the current month.
            if (!preg_match('/^\d{4}-\d{2}$/', (string) $month) || $month < date('Y-m')) {
                $month = date('Y-m');
            }

            $employeeId = $request->get('employee_id');

            $selectedEmployee = null;
            $schedule = [];
            $shifts = [];
            if ($employeeId) {
                $selectedEmployee = Employee::find($employeeId);
                $schedule = $selectedEmployee?->monthlyShiftSchedule(date('m', strtotime($month)), date('Y', strtotime($month))) ?? [];

                // Only rostering shifts of the employee's own branch can be scheduled.
                $shifts = ShiftType::where('is_shift', 1)
                    ->where('branch_id', $selectedEmployee?->branch_id)
                    ->with('shiftTimes')
                    ->get();
            }

            return view('employeeshift.index', compact('employees', 'selectedEmployee', 'schedule', 'shifts', 'month'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function get(Request $request)
    {
        if (!\Auth::user()->can('Manage Shift')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $employee = Employee::where('is_active', 1)->where('is_shift', 1)->find($request->get('employee_id'));
        if (!$employee || !$this->allowedBranchIds()->contains($employee->branch_id)) {
            return response()->json(['schedule' => [], 'shifts' => []]);
        }

        $month = $request->get('month', date('Y-m'));
        $schedule = $employee->monthlyShiftSchedule(date('m', strtotime($month)), date('Y', strtotime($month)));

        // Only rostering shifts of the employee's own branch can be scheduled.
        $shifts = ShiftType::where('is_shift', 1)
            ->where('branch_id', $employee->branch_id)
            ->with(['shiftTimes' => function ($q) {
                $q->select('id', 'shift_type_id', 'days', 'is_working', 'start_time', 'end_time');
            }])
            ->get(['id', 'name', 'branch_id'])
            ->map(function ($shift) {
                $shift->times = $shift->shiftTimes->keyBy('days');

                return $shift;
            });

        $scheduleMap = [];
        foreach ($schedule as $date => $rows) {
            $entries = [];
            foreach ($rows as $row) {
                $entries[$row->shift_type_id] = $row->end_date ?? $date;
            }
            $scheduleMap[$date] = $entries;
        }

        return response()->json(['schedule' => $scheduleMap, 'shifts' => $shifts]);
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Manage Shift')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required|exists:employees,id',
                    'month' => 'required',
                    'schedule' => 'nullable|array',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $employee = Employee::where('is_active', 1)->where('is_shift', 1)->find($request->employee_id);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => __('Employee not found.')], 404);
            }

            if (!$this->allowedBranchIds()->contains($employee->branch_id)) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
            }

            // Only rostering shifts of the employee's own branch are schedulable.
            $validShiftIds = ShiftType::where('is_shift', 1)
                ->where('branch_id', $employee->branch_id)
                ->pluck('id')
                ->toArray();

            $month = $request->month;
            $start = date('Y-m-01', strtotime($month . '-01'));
            $end = date('Y-m-t', strtotime($month . '-01'));

            // Past dates are locked and can no longer be added, changed, or removed.
            $today = date('Y-m-d');

            $newRows = [];
            $schedule = $request->schedule ?? [];
            foreach ($schedule as $date => $entries) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < $start || $date > $end) {
                    continue;
                }

                if ($date < $today) {
                    continue;
                }

                // Entries can be a list of shift ids ([id]) or a map of shiftId => endDate.
                if (is_array($entries) && array_is_list($entries)) {
                    $entries = array_fill_keys($entries, null);
                }

                foreach ($entries as $shiftTypeId => $endDate) {
                    if (!in_array((int) $shiftTypeId, $validShiftIds)) {
                        continue;
                    }

                    $shiftTypeId = (int) $shiftTypeId;

                    if ($endDate === null || $endDate === '') {
                        $endDate = $date;
                    }

                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $endDate)) {
                        $endDate = $date;
                    }

                    if ($endDate < $date) {
                        // end date cannot be before the start date.
                        return response()->json(['success' => false, 'message' => __('End date cannot be before the shift start date.')], 422);
                    }

                    $newRows[] = [
                        'employee_id' => $employee->id,
                        'date' => $date,
                        'end_date' => $endDate,
                        'shift_type_id' => $shiftTypeId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            \DB::transaction(function () use ($employee, $start, $end, $today, $newRows) {
                // Only the editable range is replaced; locked past dates stay intact.
                EmployeeShiftSchedule::where('employee_id', $employee->id)
                    ->whereBetween('date', [$start, $end])
                    ->where('date', '>=', $today)
                    ->delete();

                if (!empty($newRows)) {
                    EmployeeShiftSchedule::insert($newRows);
                }
            });

            return response()->json(['success' => true, 'message' => __('Shift schedule successfully saved.')]);
        } else {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }
    }

    private function allowedBranchIds()
    {
        $ids = collect();
        $branch = Branch::find(\Auth::user()->branch_id);

        if (\Auth::user()->type == 'company') {
            // Company (pusat) → akses semua branch
            $ids = Branch::pluck('id');
        } elseif ($branch) {
            // HR dengan branch → hanya branch sendiri + anak
            $ids->push($branch->id);

            foreach ($branch->childBranchFlatten() ?? [] as $child) {
                $ids->push($child->id);
            }
        } else {
            // HR tanpa branch → akses semua branch
            $ids = Branch::pluck('id');
        }

        return $ids;
    }
}
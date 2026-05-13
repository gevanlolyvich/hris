<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveReportExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
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

        $data       = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->get() : Leave::get();
        $employees  = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->orderby('name', 'asc')->get() : Employee::orderby('name', 'asc')->get();

        foreach ($employees as $employee) {

            $approved       = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->where('employee_id', $employee->id)->where('status', 'Approved') : Leave::where('employee_id', $employee->id)->where('status', 'Approved');
            $reject         = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->where('employee_id', $employee->id)->where('status', 'Reject') : Leave::where('employee_id', $employee->id)->where('status', 'Reject');
            $pending        = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->where('employee_id', $employee->id)->where('status', 'Pending') : Leave::where('employee_id', $employee->id)->where('status', 'Pending');
            $totalApproved  = $totalReject = $totalPending = 0;

            $approved = $approved->count();
            $reject   = $reject->count();
            $pending  = $pending->count();

            $totalApproved += $approved;
            $totalReject   += $reject;
            $totalPending  += $pending;

            $employeeLeave['approved'] = $approved;
            $employeeLeave['reject']   = $reject;
            $employeeLeave['pending']  = $pending;


            $leaves[] = $employeeLeave;
        }
        foreach ($data as $k => $leave) {
            $user_id = $leave->employees->user_id;
            $user = User::where('id', $user_id)->first();
            $data[$k]["employee_id"] = !empty($leave->employees) ? $leave->employees->employee_id : '';
            $data[$k]["employee"] = (!empty($leave->employees->name)) ? $leave->employees->name : '';

            $data[$k]["approved_leaves"] = $leaves[$k]['approved'] == 0 ? '0' : $leaves[$k]['approved'];
            $data[$k]["rejected_leaves"] = $leaves[$k]['reject'] == 0 ? '0' : $leaves[$k]['reject'];
            $data[$k]["pending_leaves"] = $leaves[$k]['pending'];
            // dd($leave['approved'],$leave['reject'] , $leave['pending']);
            

            unset($data[$k]['id'], $data[$k]['leave_type_id'], $data[$k]['start_date'], $data[$k]['end_date'], $data[$k]['applied_on'], $data[$k]['total_leave_days'], $data[$k]['leave_reason'], $data[$k]['created_at'], $data[$k]['created_by'], $data[$k]['remark'], $data[$k]['status'], $data[$k]['updated_at'], $data[$k]['account_id']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            __("Employee ID"),
            __("Employee"),
            __("Approved Leaves "),
            __("Rejected Leaves"),
            __("Pending Leaves"),
        ];
    }
}

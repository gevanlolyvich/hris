<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $user       = \Auth::user();

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
            if (\Auth::user()->type == 'employee')
            {
                 $employee = Employee::where('user_id', '=', $user->id)->first();
                
                    $data= Leave::where('employee_id', '=', $employee->id)->get();
                    
                    foreach($data as $k=>$leave)
                    {    
                        
                        
                        $data[$k]["employee_id"]=Employee::employee_name($leave->employee_id);
                        $data[$k]["leave_type_id"]= !empty(\Auth::user()->getLeaveType($leave->leave_type_id))?\Auth::user()->getLeaveType($leave->leave_type_id)->title:'';
                        $data[$k]["created_by"]=Employee::login_user($leave->created_by);
                        unset($leave->created_at,$leave->updated_at);
                    }
                
            }
            else{  
                $employee_id    = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->get()->pluck('id')->toArray() : Employee::get()->pluck('id')->toArray();
                $data           = $branch_id?->isNotEmpty() ? Leave::whereIn('employee_id', $employee_id)->get() : Leave::get();
                foreach($data as $k=>$leave)
                {    
                    
                    
                    $data[$k]["employee_id"]    = Employee::employee_name($leave->employee_id);
                    $data[$k]["leave_type_id"]  = !empty(\Auth::user()->getLeaveType($leave->leave_type_id))?\Auth::user()->getLeaveType($leave->leave_type_id)->title:'';
                    $data[$k]["created_by"]     = Employee::login_user($leave->created_by);
                    unset($leave->created_at,$leave->updated_at);
                }
                return $data;
                
            }
        
            return $data;
    }

    public function headings(): array
    {
        return [
            __("ID"),
            __("Employee Name"),
            __("Leave Type "),
            __("Applied On"),
            __("Start Date"),
            __("End Date"),
            __("Total Leaves Days"),
            __("Leave Reason"),
            __("Remark"),
            __("Location"),
            __("Document"),
            __("Note"),
            __("Status"),
            __("Created By")
        ];
    }
}

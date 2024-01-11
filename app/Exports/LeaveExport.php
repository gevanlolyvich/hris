<?php

namespace App\Exports;

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
        $data       = !empty(\Auth::user()?->branch_id) ? Leave::whereHas('employees', function ($query) { $query->where('branch_id', \Auth::user()->branch_id); })->get() : Leave::get();
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
                $employee_id    = !empty(\Auth::user()?->branch_id) ? Employee::where('branch_id', \Auth::user()?->branch_id)->get()->pluck('id')->toArray() : Employee::get()->pluck('id')->toArray();
                $data           = !empty(\Auth::user()?->branch_id) ? Leave::whereIn('employee_id', $employee_id)->get() : Leave::get();
                foreach($data as $k=>$leave)
                {    
                    
                    
                    $data[$k]["employee_id"]=Employee::employee_name($leave->employee_id);
                    $data[$k]["leave_type_id"]= !empty(\Auth::user()->getLeaveType($leave->leave_type_id))?\Auth::user()->getLeaveType($leave->leave_type_id)->title:'';
                    $data[$k]["created_by"]=Employee::login_user($leave->created_by);
                    unset($leave->created_at,$leave->updated_at);
                }
                return $data;
                
            }
        
            return $data;
    }

    public function headings(): array
    {
        return [
            "ID",
            "Employee Name",
            "Leave Type ",
            "Applied On",
            "Start Date",
            "End Date",
            "Total Leaves Days",
            "Leave Reason",
            "Remark",
            "Location",
            "Document",
            "Note",
            "Status",
            "Created By"
        ];
    }
}

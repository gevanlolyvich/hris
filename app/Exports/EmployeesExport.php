<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Bank;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesExport implements FromCollection, WithHeadings
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

        $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->get() : Employee::get();
        $data = collect();
        foreach($employees as $employee)
        {
            // unset($employees->id,$employees->user_id,$employees->documents,$employees->tax_payer_id,$employees->is_active,$employees->created_at,$employees->updated_at);

            // $data[$k]["branch_id"]=!empty($employees->branch->name);
            // $data[$k]["department_id"]=!empty($employees->department->name);
            // $data[$k]["designation_id"]= !empty($employees->designation) ? $employees->designation->name : '-';
            // $data[$k]["salary_type"]=!empty($employees->salary_type) ? $employees->salaryType->name :'-';
            // $data[$k]["salary"]=Employee::employee_salary($employees->salary);
            // $data[$k]["created_by"]=Employee::login_user($employees->created_by);

            $bank       = Bank::find($employee->bank_id);

            $data->push([
                $employee->name,
                $employee->dob ?? '-',
                $employee->gender ?? '-',
                $employee->phone ?? '-',
                $employee->address ?? '-',
                $employee->email ?? '-',
                $employee->personel_id ?? '-',
                !empty(\Auth::user()->getBranch($employee?->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : '-',
                !empty(\Auth::user()->getDepartment($employee?->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '-',
                !empty(\Auth::user()->getDesignation($employee?->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '-',
                $employee->company_doj ?? '-',
                $employee->account_holder_name ?? '-',
                $employee->account_number ?? '-',
                $bank->name ?? '-',
                $bank->code ?? '-',
                $employee->tax_payer_id ?? '-',
                $employee->salary ?? '-',
            ]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            "Name",
            "Date of Birth",
            "Gender",
            "Phone Number",
            "Address",
            "Email ID",
            "Employee ID",
            "Branch",
            "Department",
            "Designation",
            "Date of Join",
            "Account Holder Name",
            "Account Number",
            "Bank Name",
            "Bank Identifier Code",
            "Tax Payer Id",
            "Salary",
        ];
    }
}

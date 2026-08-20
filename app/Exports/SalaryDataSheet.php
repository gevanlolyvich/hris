<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalaryDataSheet implements FromCollection, WithHeadings, WithTitle
{
    // Indeks kolom template (agar sinkron dengan proses import).
    public const COL_EMPLOYEE_ID = 0;
    public const COL_NAME = 1;
    public const COL_SALARY = 2;
    public const COL_SALARY_TYPE = 3;

    public const COL_ALLOWANCE_OPTION = 4;
    public const COL_ALLOWANCE_TITLE = 5;
    public const COL_ALLOWANCE_RECURRING = 6;
    public const COL_ALLOWANCE_PERIOD = 7;
    public const COL_ALLOWANCE_AMOUNT = 8;

    public const COL_COMMISSION_TITLE = 9;
    public const COL_COMMISSION_TYPE = 10;
    public const COL_COMMISSION_RECURRING = 11;
    public const COL_COMMISSION_PERIOD = 12;
    public const COL_COMMISSION_AMOUNT = 13;

    public const COL_OTHERPAYMENT_TITLE = 14;
    public const COL_OTHERPAYMENT_TYPE = 15;
    public const COL_OTHERPAYMENT_RECURRING = 16;
    public const COL_OTHERPAYMENT_PERIOD = 17;
    public const COL_OTHERPAYMENT_AMOUNT = 18;

    public const COL_LOAN_TITLE = 19;
    public const COL_LOAN_OPTION = 20;
    public const COL_LOAN_RECURRING = 21;
    public const COL_LOAN_PERIOD = 22;
    public const COL_LOAN_PERIOD_START = 23;
    public const COL_LOAN_PERIOD_END = 24;
    public const COL_LOAN_TYPE = 25;
    public const COL_LOAN_AMOUNT = 26;
    public const COL_LOAN_REASON = 27;

    public const COL_BPJS_OPTION = 28;
    public const COL_BPJS_RECURRING = 29;
    public const COL_BPJS_TYPE = 30;
    public const COL_BPJS_AMOUNT = 31;

    public const COL_DEDUCTION_OPTION = 32;
    public const COL_DEDUCTION_TITLE = 33;
    public const COL_DEDUCTION_TYPE = 34;
    public const COL_DEDUCTION_RECURRING = 35;
    public const COL_DEDUCTION_PERIOD = 36;
    public const COL_DEDUCTION_AMOUNT = 37;

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

        $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get() : Employee::where('is_active', 1)->orderby('name', 'asc')->get();

        $data = collect();
        foreach ($employees as $employee) {
            $row = array_fill(0, count($this->headings()), '');
            $row[self::COL_EMPLOYEE_ID] = $employee->employee_id;
            $row[self::COL_NAME] = $employee->name;

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "Employee Id",
            "Name",
            "Salary*",
            "Salary Type",
            "Allowance Option",
            "Allowance Title",
            "Allowance Recurring (0/1/2)",
            "Allowance Period (YYYY-MM)",
            "Allowance Amount",
            "Commission Title",
            "Commission Type",
            "Commission Recurring (0/1)",
            "Commission Period (YYYY-MM)",
            "Commission Amount",
            "Other Payment Title",
            "Other Payment Type",
            "Other Payment Recurring (0/1)",
            "Other Payment Period (YYYY-MM)",
            "Other Payment Amount",
            "Loan Title",
            "Loan Option",
            "Loan Recurring (0/1)",
            "Loan Period (YYYY-MM)",
            "Loan Period Start (YYYY-MM)",
            "Loan Period End (YYYY-MM)",
            "Loan Type",
            "Loan Amount",
            "Loan Reason",
            "BPJS Option",
            "BPJS Recurring (0/1)",
            "BPJS Type",
            "BPJS Amount (%)",
            "Deduction Option",
            "Deduction Title",
            "Deduction Type",
            "Deduction Recurring (0/1)",
            "Deduction Period (YYYY-MM)",
            "Deduction Amount",
        ];
    }

    public function title(): string
    {
        return 'Data';
    }
}

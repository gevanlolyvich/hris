<?php

namespace App\Exports;


use App\Models\Employee;
use App\Models\PaySlip;
use App\Models\Bank;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Log;

class PayslipExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function collection()
    {
        $request = $this->data;

        $formated_month_year = $request->month;
        if(empty($formated_month_year)){
            $formated_month_year = date('Y') . '-' . date('m', strtotime('last month'));
        }

        $data = !empty(\Auth::user()->branch_id) ? PaySlip::whereHas('employees', function ($query) { $query->where('branch_id', \Auth::user()->branch_id); })->where('salary_month', '=', $formated_month_year) : PaySlip::where('salary_month', '=', $formated_month_year);
        $data=$data->get();
        $result = array();

        foreach($data as $k => $payslip)
        {
            $bank       = Bank::find($payslip?->employees?->bank_id);
            $result[]   = array(
                'employee_id'=> !empty($payslip->employees) ? $payslip->employees->employee_id : '-',
                'employee_name' => (!empty($payslip->employees)) ? $payslip->employees->name : '-',
                'basic_salary' => \Auth::user()->priceFormat($payslip->basic_salary),
                'net_salary' =>  \Auth::user()->priceFormat($payslip->net_payble),
                'status' =>  $payslip->status == 0 ? 'UnPaid' :  'Paid',
                'account_holder_name' =>  (empty($payslip->employees)) ? '-' : $payslip?->employees?->account_holder_name ?? '-',
                'account_number' =>  (empty($payslip->employees)) ? '-' : $payslip?->employees?->account_number ?? '-',
                'bank_name' =>  $bank->name ?? '-',
                'bank_identifier_code' => $bank->code ?? '-',
                'tax_payer_id' =>  (empty($payslip->employees)) ? '-' : $payslip->employees->tax_payer_id ?? '-',

            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            "EMP ID",
            "Name",
            "Salary",
            "Net Salary",
            "Status",
            "Account Holder Name",
            "Account Number",
            "Bank Name",
            "Bank Identifier Code",
            "Tax Payer Id",
        ];
    }
}

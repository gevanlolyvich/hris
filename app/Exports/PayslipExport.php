<?php

namespace App\Exports;


use App\Models\Employee;
use App\Models\PaySlip;
use App\Models\Bank;
use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\Log;

class PayslipExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
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

        if ($request->branch) {
            $data = PaySlip::whereHas('employees', function ($query) use ($request) { $query->where('branch_id', $request->branch); })->where('salary_month', '=', $formated_month_year);
        } else {
            $data = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->where('salary_month', '=', $formated_month_year) : PaySlip::where('salary_month', '=', $formated_month_year);
        }
        $data=$data->get();
        $result = array();

        foreach($data as $k => $payslip)
        {
            $bank       = Bank::find($payslip?->employees?->bank_id);
            $result[]   = array(
                'employee_id'=> !empty($payslip->employees) ? $payslip->employees->employee_id : '-',
                'employee_name' => (!empty($payslip->employees)) ? $payslip->employees->name : '-',
                'basic_salary' => \Auth::user()->priceFormat($payslip->basic_salary),
                'bruto' => \Auth::user()->priceFormat($payslip->bruto),
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
            "Bruto",
            "Net Salary",
            "Status",
            "Account Holder Name",
            "Account Number",
            "Bank Name",
            "Bank Identifier Code",
            "Tax Payer Id",
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Freeze Cells
                $sheet->freezePane('C2');

                // Apply Style To Header
                $sheet->getStyle('A1:K1')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
            },
        ];
    }
}

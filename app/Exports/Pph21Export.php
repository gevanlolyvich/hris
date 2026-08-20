<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Pph21;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Pph21Export implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
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

        $formate_month_year = $request->month ?? date('Y-m', strtotime(date('Y-m') . ' -1 month'));
        $month = date('m', strtotime($formate_month_year));
        $year  = date('Y', strtotime($formate_month_year));

        $start_date = date($year . '-' . $month . '-01');
        $end_date   = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

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

        if (\Auth::user()->type == 'employee') {
            $data = Pph21::whereBetween('date', [$start_date, $end_date])
                ->where('employee_id', \Auth::user()->employee?->id);
        } elseif ($request->branch) {
            $data = Pph21::whereBetween('date', [$start_date, $end_date])
                ->whereHas('employee', function ($query) use ($request) { $query->where('branch_id', $request->branch); });
        } else {
            $data = $branch_id?->isNotEmpty()
                ? Pph21::whereBetween('date', [$start_date, $end_date])->whereHas('employee', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })
                : Pph21::whereBetween('date', [$start_date, $end_date]);
        }

        $data = $data->withAggregate('employee', 'name')->orderBy('date', 'desc')->orderBy('employee_name', 'asc')->get();

        $result = array();

        foreach ($data as $pph) {
            $result[] = array(
                'employee' => $pph?->employee?->name ?? '-',
                'branch'   => $pph?->employee?->branch?->name ?? '-',
                'ptkp'     => $pph?->ptkp ?? '-',
                'date'     => $pph?->date ?? '-',
                'bruto'    => number_format($pph?->bruto ?? 0, 2),
                'pph21'    => number_format($pph?->pph21 ?? 0, 2),
            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            "Employee",
            "Branch",
            "PTKP",
            "Date",
            "Bruto",
            "PPh 21",
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Freeze Cells
                $sheet->freezePane('A2');

                // Apply Style To Header
                $sheet->getStyle('A1:F1')->applyFromArray([
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
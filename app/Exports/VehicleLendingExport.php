<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\VehicleLending;
use App\Models\Utility;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class VehicleLendingExport implements FromCollection, WithEvents, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    private $query;
    private $total_data;

    // Modify the constructor to accept parameters
    public function __construct($query)
    {
        $this->query = json_decode($query);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data         = collect();

        // Setup Heading
        $settings     = Utility::settings();
        $data->push([ __('Vehicle Lending'), '', $this->query->timeFrame]); // Excel Title
        $data->push(['']);
        $data->push([ // Heading
          'No',
          __('Date'),
          __('End Date'),
          __('Employee'),
          __('Vehicle'),
          __('Police No'),
          __('Vehicle Type'),
          __('Approved By'),
          __('Pick Up Time'),
          __('Pick Up KM'),
          __('Pick Up Emoney Balance'),
          __('Pick Up File'),
          __('Return Time'),
          __('Return KM'),
          __('Return Emoney Balance'),
          __('Return File'),
        ]);

        // Set Up Data
        if (strlen($this->query?->timeFrame) > 7) {
          $lendings = VehicleLending::where('date', '<=', $this->query->timeFrame)->where('end_date', '>=', $this->query->timeFrame);
        } else {
          $month = date('m', strtotime($this->query->timeFrame));
          $year  = date('Y', strtotime($this->query->timeFrame));
          $lendings = VehicleLending::whereMonth('date', $month)->whereYear('date', $year);
        }
        $lendings = $lendings->where('status', 'Approved')->orderBy('date', 'ASC')->get();

        foreach ($lendings as $index => $lending) {
          $data->push([
            $index + 1,
            $lending->date,
            $lending->end_date,
            $lending?->requester?->name ?? '-',
            $lending?->vehicle?->name ?? '-',
            $lending?->vehicle?->police_no ?? '-',
            $lending?->vehicle?->type ?? '-',
            $lending?->approver?->name ?? '-',
            $lending->pickup_time,
            $lending->pickup_km,
            'Rp '. $lending->pickup_emoney_balance,
            $lending->pickup_file_1 . ' ; ' . $lending->pickup_file_2,
            $lending->return_time,
            $lending->return_km,
            'Rp '. $lending->return_emoney_balance,
            $lending->return_file_1 . ' ; ' . $lending->return_file_2,
          ]);
        }

        $this->total_data = count($data) + 1;

        return $data;
    }

    public function title(): string
    {
        return __('Vehicle Lending');
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->mergeCells('A2:B2');

                $sheet->getStyle('A2:C2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => '14',
                    ],
                ]);

                $sheet->getStyle('A4:P4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => '12',
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                        ]
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => 'B7BCEE',
                        ],
                        'endColor' => [
                            'argb' => 'CCCDDA',
                        ],
                    ],
                ]);

                $sheet->getStyle("A5:P{$this->total_data}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);

                // Freeze Cells
                $sheet->freezePane('A5');

                // if ($sheet->getHighestRow() > 1) {
                //     foreach ($sheet->getRowIterator(2) as $row) {
                      
                //     }
                // }
            },
        ];
    }
}

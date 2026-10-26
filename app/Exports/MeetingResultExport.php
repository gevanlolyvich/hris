<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\MeetingNew;
use App\Models\Utility;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MeetingResultExport implements FromCollection, WithHeadings, WithEvents
{
    public $docCells = [];

    public function collection()
    {
        $currentEmployee = Employee::where('user_id', \Auth::id())->first();
        $accessibleDeptIds = [11, 12, 16];

        $meetings = MeetingNew::with('attendees.employee', 'results.employee')
            ->orderBy('id', 'desc')
            ->get();

        if (\Auth::user()->type != 'company' && $currentEmployee && !in_array($currentEmployee->department_id, $accessibleDeptIds)) {
            $attendeeMeetingIds = MeetingNew::whereHas('attendees', function ($q) use ($currentEmployee) {
                $q->where('employee_id', $currentEmployee->id);
            })->pluck('id')->toArray();

            $deptMeetingIds = MeetingNew::whereHas('attendees.employee', function ($q) use ($currentEmployee) {
                $q->where('department_id', $currentEmployee->department_id);
            })->pluck('id')->toArray();

            $meetingIds = array_unique(array_merge($attendeeMeetingIds, $deptMeetingIds));
            $meetings = MeetingNew::with('attendees.employee', 'results.employee')
                ->whereIn('id', $meetingIds)
                ->orderBy('id', 'desc')
                ->get();
        }

        $rows = collect();
        $this->docCells = [];

        foreach ($meetings as $index => $meeting) {
            $docUrls = [];
            if ($meeting->document && is_array($meeting->document)) {
                foreach ($meeting->document as $doc) {
                    $docUrls[] = Utility::get_file('uploads/meetingNew') . '/' . $doc;
                }
            }

            $docCellText = count($docUrls) > 0 ? implode("\n", $docUrls) : '-';

            $currentRow = 2 + $rows->count();

            $rows->push([
                $index + 1,
                $meeting->title,
                $meeting->meeting_type,
                $meeting->meeting_date->format('d-M-y'),
                \Carbon\Carbon::parse($meeting->meeting_time)->format('H:i'),
                $docCellText,
            ]);

            if (count($docUrls) > 0) {
                $this->docCells[$currentRow] = $docUrls;
            }

            $attendeeRow = ['', 'employee attendances :'];
            $resultRow = ['', 'hasil meeting :'];

            foreach ($meeting->attendees as $attendee) {
                $attendeeRow[] = $attendee->employee ? $attendee->employee->name : '-';

                $res = $meeting->results
                    ->where('employee_id', $attendee->employee_id)
                    ->whereNotNull('filled_at')
                    ->first();

                if ($res) {
                    $plain = preg_replace('/<\/p>|<br\s*\/?>/i', "\n", $res->content ?? '');
                    $plain = strip_tags($plain);
                    $plain = html_entity_decode($plain);
                    $plain = preg_replace('/[ \t]+/', ' ', $plain);
                    $plain = preg_replace('/\n{3,}/', "\n\n", $plain);
                    $resultRow[] = trim($plain);
                } else {
                    $resultRow[] = 'Belum diisi';
                }
            }

            $rows->push($attendeeRow);
            $rows->push($resultRow);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Judul Meeting',
            'Tipe',
            'Tanggal',
            'Time',
            'Document',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'D9E1F2',
                        ],
                    ],
                ]);

                if ($lastRow > 1) {
                    $sheet->getStyle("A1:F{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(45);
                foreach (['C', 'D', 'E', 'F'] as $col) {
                    $sheet->getColumnDimension($col)->setWidth(40);
                }

                if ($lastRow > 1) {
                    $sheet->getStyle("B2:F{$lastRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
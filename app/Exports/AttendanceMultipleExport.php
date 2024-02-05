<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\LeaveType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceMultipleExport implements WithMultipleSheets
{
    private $query;

    // Modify the constructor to accept parameters
    public function __construct($urlParameters)
    {
        $this->query = json_decode($urlParameters);
    }

    public function sheets(): array
    {
        $sheets = [];

        array_push($sheets, new MarkedAttendanceExport($this->query));
        array_push($sheets, new MarkedAttendanceSummaryExport($this->query));

        return $sheets;
    }
}

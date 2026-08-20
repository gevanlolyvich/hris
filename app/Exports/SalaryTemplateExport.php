<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalaryTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SalaryDataSheet(),
            new SalaryInstructionsSheet(),
        ];
    }
}

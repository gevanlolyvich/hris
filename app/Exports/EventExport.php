<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = !empty(\Auth::user()?->branch_id) ? Event::where('branch_id', \Auth::user()->branch_id)->orderby('start_date', 'DESC')->get() : Event::orderby('start_date', 'DESC')->get();

        foreach ($data as $k => $events) {
            $data[$k]["branch_id"]     = Branch::where('id',$events->branch_id)->pluck('name')->first();
            // dd(json_decode($events->department_id));
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "ID",
            "Branch Id",
            "Department Id",
            "Employee Id",
            "Title",
            "Start Date",
            "End Date",
            "Color",
            "Description",
            "Created By",
            "Created At",
            "Updated At",
        ];
    }
}

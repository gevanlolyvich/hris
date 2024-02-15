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
        $data = $branch_id?->isNotEmpty() ? Event::whereIn('branch_id', $branch_id)->orderby('start_date', 'DESC')->get() : Event::orderby('start_date', 'DESC')->get();

        foreach ($data as $k => $events) {
            $data[$k]["branch_id"]     = Branch::where('id',$events->branch_id)->pluck('name')->first();
            // dd(json_decode($events->department_id));
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            __("ID"),
            __("Branch Id"),
            __("Department Id"),
            __("Employee Id"),
            __("Title"),
            __("Start Date"),
            __("End Date"),
            __("Color"),
            __("Description"),
            __("Created By"),
            __("Created At"),
            __("Updated At"),
        ];
    }
}

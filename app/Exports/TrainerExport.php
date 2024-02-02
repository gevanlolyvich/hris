<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Trainer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrainerExport implements FromCollection,WithHeadings
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

        $data = $branch_id?->isNotEmpty() ? Trainer::whereIn('branch', $branch_id)->get() : Trainer::get();
        foreach($data as $k=>$trainer)
        {
            $data[$k]["branch"]=!empty($trainer->branches)?$trainer->branches->name:'';
            $data[$k]["created_by"]=Employee::login_user($trainer->created_by); 
            unset($trainer->created_at,$trainer->updated_at);
        }
        return $data;
    }
    public function headings(): array
    {
        return [
            __("ID"),
            __("Branch Name"),
            __("First Name"),
            __("Last Name"),
            __("Contact"),
            __("Email ID"),
            __("Address"),
            __("Expeience"),
            __("Created By"),
        ];
    }
}

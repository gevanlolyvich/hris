<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\TrainingType;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrainingExport implements FromCollection, WithHeadings
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

        $data = $branch_id?->isNotEmpty() ? Training::whereIn('branch', $branch_id)->get() : Training::get();

        foreach ($data as $k => $training) {
            unset($training->created_at,$training->updated_at);
            $data[$k]["branch"] = $training->branch_ref?->name ?? '-';
            
            $trainer_option     = $training->trainer_option;
            if ($trainer_option == 0) {
                $data[$k]["trainer_option"] = 'Internal';
            } else {
                $data[$k]["trainer_option"] = 'External';
            }

            $data[$k]["training_type"]      = $training->type?->name ?? '-';
            $data[$k]["trainer"]            = $training->trainer_ref?->firstname ?? '-';
            $data[$k]["employee"]           = $training->employee_ref?->name ?? '-';
            $data[$k]["status"]             = Training::status($training->status);
            $data[$k]["performance"]        = Training::performance($training->performance);
            $data[$k]["created_by"]         = Employee::login_user($training->created_by); 
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            __("ID"),
            __("Branch Name"),
            __("Trainer Option"),
            __("Trainer Type"),
            __("Trainer"),
            __("Trainer Cost"),
            __("Employee Name"),
            __("Start Date"),
            __("End Date"),
            __("Description"),
            __("Performance"),
            __("status"),
            __("Remarks"),
            __("Created By"),
        ];
    }
}

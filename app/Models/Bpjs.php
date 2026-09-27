<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bpjs extends Model
{
    protected $fillable = [
        'employee_id',
        'bpjs_option',
        'is_recurring',
        'is_prorated',
        'type',
        'amount',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id')->first();
    }

    public function bpjs_option()
    {
        return $this->hasOne('App\Models\BpjsOption', 'id', 'bpjs_option')->first();
    }

    /**
     * Nominal BPJS yang sebenarnya dipotong untuk data ini.
     *
     * Memakai formula yang sama dengan allowance prorate: percentage dihitung dari
     * gaji dasar, lalu dikalikan fixed_rate (employee type Fixed) atau
     * total_present_days (tipe lainnya).
     *
     * @param  \App\Models\Employee|null  $employee
     * @param  float|null  $fixed_rate
     * @param  float|int  $total_present_days
     * @return float
     */
    public function resolvedAmount($employee = null, $fixed_rate = null, $total_present_days = null)
    {
        $employee = $employee ?: $this->employee();
        $salary   = $employee?->salary ?: 0;

        $amount = $this->type == 'percentage' ? $this->amount * $salary / 100 : $this->amount;

        if (!$this->is_prorated) {
            return $amount;
        }

        if (is_null($fixed_rate) || is_null($total_present_days)) {
            $fixed_rate        = $fixed_rate ?? 1;
            $total_present_days = $total_present_days ?? 0;
        }

        return $employee?->employeeType?->type == 'Fixed' ? $amount * $fixed_rate : $amount * $total_present_days;
    }
}

<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\FlexibleTime;
use App\Models\ShiftTime;
use Carbon\Carbon;

class FlexibleTimeService
{
    public const HEAD_OFFICE_BRANCH = 'Head Office';

    public const FULLTIME_NAMES = ['fulltime', 'full time'];

    /**
     * Eligibility: fulltime employee (employee type or shift type name) at Head Office.
     */
    public function isEligible(Employee $employee): bool
    {
        if ($employee->is_shift) {
            return false;
        }

        if (! $this->isFulltime($employee)) {
            return false;
        }

        return $this->isHeadOffice($employee);
    }

    public function isFulltime(Employee $employee): bool
    {
        $typeNames = [];

        if ($employee->employeeType?->name) {
            $typeNames[] = strtolower(trim($employee->employeeType->name));
        }

        if ($employee->employeeType?->type) {
            $typeNames[] = strtolower(trim($employee->employeeType->type));
        }

        if ($employee->shift_type?->name) {
            $typeNames[] = strtolower(trim($employee->shift_type->name));
        }

        if ($typeNames === []) {
            return false;
        }

        foreach ($typeNames as $name) {
            if (in_array($name, self::FULLTIME_NAMES, true)) {
                return true;
            }
        }

        return false;
    }

    public function isHeadOffice(Employee $employee): bool
    {
        if (! $employee->branch_id) {
            return false;
        }

        $branch = Branch::find($employee->branch_id);

        return $branch && strtolower(trim($branch->name)) === strtolower(self::HEAD_OFFICE_BRANCH);
    }

    /**
     * The normal (master schedule) work times for an employee on the given date.
     * Returns ['start_time' => 'HH:MM:SS', 'end_time' => 'HH:MM:SS'] or null when
     * the day is not a working day / no schedule is defined.
     *
     * Rostered (is_shift) employees resolve the shift scheduled for that date;
     * fixed employees use their default shift type.
     */
    public function getNormalWorkSchedule(Employee $employee, string $workDate): ?array
    {
        $dayName = date('l', strtotime($workDate));

        $shiftTypeId = $employee->is_shift
            ? ($employee->scheduledShiftIdsForDate($workDate)[0] ?? null)
            : $employee->shift_type_id;

        if (! $shiftTypeId) {
            $shiftTypeId = $employee->shift_type_id;
        }

        if (! $shiftTypeId) {
            return null;
        }

        $shiftTime = ShiftTime::where('shift_type_id', $shiftTypeId)
            ->where('days', $dayName)
            ->first();

        if (! $shiftTime || ! $shiftTime->is_working || ! $shiftTime->start_time || ! $shiftTime->end_time) {
            return null;
        }

        return [
            'start_time' => $this->normalizeTime($shiftTime->start_time),
            'end_time' => $this->normalizeTime($shiftTime->end_time),
        ];
    }

    /**
     * Effective work schedule for an employee on a date: the master schedule
     * overridden by an approved Flexible Time request for that date.
     *
     * Central entry point for all attendance lateness calculations.
     * Returns ['start_time' => 'HH:MM:SS', 'end_time' => 'HH:MM:SS', 'flexible' => bool]
     * or null when no schedule exists for the date.
     */
    public function getEffectiveWorkSchedule(int $employeeId, string $workDate): ?array
    {
        $employee = Employee::find($employeeId);

        if (! $employee) {
            return null;
        }

        $normal = $this->getNormalWorkSchedule($employee, $workDate);

        $flexible = FlexibleTime::where('employee_id', $employeeId)
            ->where('date', $workDate)
            ->where('status', FlexibleTime::STATUS_APPROVED)
            ->first();

        if ($flexible && $flexible->approved_start_time) {
            return [
                'start_time' => $this->normalizeTime($flexible->approved_start_time),
                'end_time' => $this->normalizeTime($flexible->approved_end_time ?: $this->addHours($flexible->approved_start_time, FlexibleTime::DURATION_HOURS)),
                'flexible' => true,
                'normal_start_time' => $normal['start_time'] ?? null,
                'normal_end_time' => $normal['end_time'] ?? null,
            ];
        }

        if (! $normal) {
            return null;
        }

        return [
            'start_time' => $normal['start_time'],
            'end_time' => $normal['end_time'],
            'flexible' => false,
            'normal_start_time' => $normal['start_time'],
            'normal_end_time' => $normal['end_time'],
        ];
    }

    /**
     * Resolve only the effective start time, falling back to the given default
     * when no schedule exists. Convenience wrapper for attendance controllers.
     */
    public function effectiveStartTime(int $employeeId, string $workDate, ?string $default = null): string
    {
        $schedule = $this->getEffectiveWorkSchedule($employeeId, $workDate);

        return $schedule['start_time'] ?? $default;
    }

    /**
     * Override a ShiftTime instance (in memory only, never saved) with the
     * approved Flexible Times for the given date. Returns the same instance
     * so it can be chained after the normal ShiftTime query.
     */
    public function applyToShiftTimes($shiftTimes, ?int $employeeId, string $workDate)
    {
        if (! $shiftTimes || ! $employeeId) {
            return $shiftTimes;
        }

        $flexible = FlexibleTime::where('employee_id', $employeeId)
            ->where('date', $workDate)
            ->where('status', FlexibleTime::STATUS_APPROVED)
            ->first();

        if ($flexible && $flexible->approved_start_time) {
            $shiftTimes->start_time = $this->normalizeTime($flexible->approved_start_time);
            $shiftTimes->end_time = $this->normalizeTime(
                $flexible->approved_end_time
 ?: $this->addHours($flexible->approved_start_time, FlexibleTime::DURATION_HOURS)
            );
        }

        return $shiftTimes;
    }

    public function effectiveEndTime(int $employeeId, string $workDate, ?string $default = null): string
    {
        $schedule = $this->getEffectiveWorkSchedule($employeeId, $workDate);

        return $schedule['end_time'] ?? $default;
    }

    /**
     * Validate a requested start time: not earlier than the normal start,
     * not later than 11:00. Returns error message or null when valid.
     */
    public function validateRequestedStart(Employee $employee, string $workDate, string $requestedStart): ?string
    {
        $normal = $this->getNormalWorkSchedule($employee, $workDate);

        if (! $normal) {
            return __('No work schedule found for this date.');
        }

        $requested = $this->normalizeTime($requestedStart);
        $normalStart = $normal['start_time'];
        $maxStart = FlexibleTime::MAX_START_TIME;

        if ($requested < $normalStart) {
            return __('Requested start time cannot be earlier than the normal start time (:normal).', ['normal' => substr($normalStart, 0, 5)]);
        }

        if ($requested > $maxStart) {
            return __('Requested start time cannot be later than :max.', ['max' => substr($maxStart, 0, 5)]);
        }

        return null;
    }

    /**
     * Compute the requested end time: requested start + fixed duration hours.
     */
    public function requestedEndFromStart(string $requestedStart): string
    {
        return $this->addHours($requestedStart, FlexibleTime::DURATION_HOURS);
    }

    /**
     * Store the normal schedule snapshot on the request for audit purposes.
     */
    public function normalScheduleSnapshot(Employee $employee, string $workDate): array
    {
        $normal = $this->getNormalWorkSchedule($employee, $workDate);

        return [
            'normal_start_time' => $normal['start_time'] ?? null,
            'normal_end_time' => $normal['end_time'] ?? null,
        ];
    }

    protected function normalizeTime(string $time): string
    {
        $time = trim($time);

        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return $time;
    }

    protected function addHours(string $time, int $hours): string
    {
        $normalized = $this->normalizeTime($time);

        return Carbon::parse($normalized)->addHours($hours)->format('H:i:s');
    }
}

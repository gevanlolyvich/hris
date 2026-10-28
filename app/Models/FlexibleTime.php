<?php

namespace App\Models;

use App\Services\FlexibleTimeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class FlexibleTime extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const MAX_START_TIME = '11:00:00';
    public const DURATION_HOURS = 9;

    protected $table = 'flexible_times';

    protected $fillable = [
        'employee_id',
        'date',
        'normal_start_time',
        'normal_end_time',
        'requested_start_time',
        'requested_end_time',
        'approved_start_time',
        'approved_end_time',
        'reason',
        'document',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FlexibleTimeLog::class, 'flexible_time_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeActiveStatuses($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Company user id 1 and HR user id 2 may view every request.
     */
    public static function canViewAll(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return (int) $user->id === 1 || (int) $user->id === 2;
    }

    /**
     * HR accounts other than user id 2 have no access at all to Flexible Time.
     */
    public static function isHrBlocked(?User $user): bool
    {
        if (! $user) {
            return true;
        }

        return $user->type === 'hr' && (int) $user->id !== 2;
    }

    /**
     * Approve/reject rights: company id 1, or the employee's direct supervisor
     * (employees.managed_by). HR other than id 2 never gets approval rights.
     */
    public static function canApprove(?User $user, FlexibleTime $flexibleTime): bool
    {
        if (! $user || self::isHrBlocked($user)) {
            return false;
        }

        if ((int) $user->id === 1) {
            return true;
        }

        $approverEmployee = $user->employee;

        if (! $approverEmployee) {
            return false;
        }

        // Self-approval is not allowed.
        if ((int) $approverEmployee->id === (int) $flexibleTime->employee_id) {
            return false;
        }

        return (int) $flexibleTime->employee?->managed_by === (int) $approverEmployee->id;
    }

    public static function canView(?User $user, FlexibleTime $flexibleTime): bool
    {
        if (! $user || self::isHrBlocked($user)) {
            return false;
        }

        if (self::canViewAll($user)) {
            return true;
        }

        $employee = $user->employee;

        if (! $employee) {
            return false;
        }

        if ((int) $employee->id === (int) $flexibleTime->employee_id) {
            return true;
        }

        // Direct supervisor can view subordinate requests.
        return (int) $flexibleTime->employee?->managed_by === (int) $employee->id;
    }

    public static function canCancel(?User $user, FlexibleTime $flexibleTime): bool
    {
        if (! $user || self::isHrBlocked($user)) {
            return false;
        }

        if (! $flexibleTime->isPending()) {
            return false;
        }

        $employee = $user->employee;

        return $employee && (int) $employee->id === (int) $flexibleTime->employee_id;
    }

    /**
     * Whether the sidebar menu entry should be shown.
     */
    public static function canAccessMenu(?User $user): bool
    {
        if (! $user || self::isHrBlocked($user)) {
            return false;
        }

        if (self::canViewAll($user)) {
            return true;
        }

        $employee = $user->employee;

        if (! $employee) {
            return false;
        }

        // Employee who may submit requests.
        if (app(FlexibleTimeService::class)->isEligible($employee)) {
            return true;
        }

        // Direct supervisor of at least one employee (may receive approvals).
        return Employee::where('managed_by', $employee->id)->exists();
    }
}

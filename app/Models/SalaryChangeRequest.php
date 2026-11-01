<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryChangeRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';

    public const SOURCE_FORM = 'form';
    public const SOURCE_IMPORT = 'import';

    public const REVIEWER_DESIGNATION_ID = 4;

    protected $fillable = [
        'employee_id',
        'old_salary',
        'new_salary',
        'source',
        'status',
        'requested_by',
        'reviewed_by',
        'note',
        'reviewed_at',
    ];

    protected $casts = [
        'old_salary' => 'float',
        'new_salary' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id')->withTrashed();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReviewed($query)
    {
        return $query->whereIn('status', [self::STATUS_APPROVED, self::STATUS_REJECTED]);
    }

    public static function isReviewer(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return (int) optional($user->employee)->designation_id === self::REVIEWER_DESIGNATION_ID;
    }
}

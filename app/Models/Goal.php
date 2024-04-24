<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'employee_id',
        'name',
        'target',
        'start_date',
        'end_date',
        'description',
        'goal',
        'progress',
        'branch_id',
        'department_id'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
    
    public function recursiveParent(): BelongsTo
    {
        return $this->parent()->with('recursiveParent');
    }
    
    public function parentFlatten()
    {
        $result = collect();
        $item   = $this->recursiveParent;
        if ($item instanceof Goal) {
            $result->push($item);
            $result = $result->merge($item->parentFlatten());
        }
        
        return $result;
    }

    public function child(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childRecursive(): HasMany
    {
        return $this->child()->with('childRecursive');
    }
    
    public function childsFlatten()
    {
        $result     = collect();
        $childs     = $this->childRecursive;
        
        foreach ($childs as $child) {
            if ($child instanceof Goal) {
                $result->push($child);
                $result = $result->merge($child->childsFlatten());
            }
        }
        
        return $result;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

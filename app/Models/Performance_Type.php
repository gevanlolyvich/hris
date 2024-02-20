<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Performance_Type extends Model
{
    // use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
        'parent_id',
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
        if ($item instanceof Performance_Type) {
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
            if ($child instanceof Performance_Type) {
                $result->push($child);
                $result = $result->merge($child->childsFlatten());
            }
        }
        
        return $result;
    }
}

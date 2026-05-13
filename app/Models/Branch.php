<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Branch extends Model
{
    protected $fillable = [
        'name',
        'parent_branch',
        'latitude',
        'longitude',
        'tolerance',
        'created_by',
    ];

    public function parentBranch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_branch', 'id');
    }
    
    public function recursiveParentBranch(): BelongsTo
    {
        return $this->parentBranch()->with('recursiveParentBranch');
    }
    
    public function parentBranchFlatten()
    {
        $result = collect();
        $item = $this->recursiveParentBranch;
        if ($item instanceof Branch) {
            $result->push($item);
            $result = $result->merge($item->parentBranchFlatten());
        }
        
        return $result;
    }
    
    public function childBranch(): HasMany
    {
        return $this->hasMany(self::class, 'parent_branch');
    }

    public function childBranchRecursive(): HasMany
    {
        return $this->childBranch()->with('childBranchRecursive');
    }
    
    public function childBranchFlatten()
    {
        $result = collect();
        $children = $this->childBranchRecursive;
        
        foreach ($children as $child) {
            if ($child instanceof Branch) {
                $result->push($child);
                $result = $result->merge($child->childBranchFlatten());
            }
        }
        
        return $result;
    }
}

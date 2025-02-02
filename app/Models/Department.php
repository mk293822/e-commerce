<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{

    public function scopePublish(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}

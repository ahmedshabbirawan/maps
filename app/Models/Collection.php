<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'description'])]
class Collection extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }
}

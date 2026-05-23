<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['collection_id', 'name', 'type'])]
class Attribute extends Model
{
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function pointValues(): HasMany
    {
        return $this->hasMany(PointValue::class);
    }
}

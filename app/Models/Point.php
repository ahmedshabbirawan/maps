<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['collection_id', 'name', 'lat', 'lng'])]
class Point extends Model
{
    protected function casts(): array
    {
        return [
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(PointValue::class);
    }

    public function valueForAttribute(int $attributeId): ?string
    {
        $pointValue = $this->values->firstWhere('attribute_id', $attributeId);

        return $pointValue?->value;
    }
}

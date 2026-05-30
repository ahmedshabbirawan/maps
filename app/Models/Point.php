<?php

namespace App\Models;

use App\Traits\HasCustomAttributes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['collection_id', 'name', 'lat', 'lng'])]
class Point extends Model
{
    use HasCustomAttributes;

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

    public function valueForAttribute(int $attributeId): ?string
    {
        if (! $this->relationLoaded('attributeValues')) {
            $this->load('attributeValues');
        }

        return $this->attributeValues->firstWhere('attribute_id', $attributeId)?->value;
    }
}

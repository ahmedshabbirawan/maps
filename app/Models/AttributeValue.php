<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['attribute_id', 'entity_type', 'entity_id', 'value'])]
class AttributeValue extends Model
{
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function getCastedValue(): mixed
    {
        $type = $this->attribute?->type ?? 'string';

        return match ($type) {
            'number' => $this->value !== null && $this->value !== '' ? (float) $this->value : null,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'date' => $this->value ? Carbon::parse($this->value) : null,
            default => $this->value,
        };
    }

    public static function serializeValue(Attribute $attribute, mixed $value): string
    {
        return match ($attribute->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'date' => $value instanceof Carbon
                ? $value->format('Y-m-d')
                : Carbon::parse($value)->format('Y-m-d'),
            'number' => (string) $value,
            default => (string) $value,
        };
    }
}

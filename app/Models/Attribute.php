<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['collection_id', 'name', 'slug', 'type', 'is_visible'])]
class Attribute extends Model
{
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Attribute $attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = static::uniqueSlugForCollection(
                    $attribute->collection_id,
                    Str::slug($attribute->name)
                );
            }
        });

        static::updating(function (Attribute $attribute) {
            if ($attribute->isDirty('name') && ! $attribute->isDirty('slug')) {
                $attribute->slug = static::uniqueSlugForCollection(
                    $attribute->collection_id,
                    Str::slug($attribute->name),
                    $attribute->id
                );
            }
        });
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public static function uniqueSlugForCollection(int $collectionId, string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: 'attribute';
        $original = $slug;
        $counter = 1;

        while (
            static::query()
                ->where('collection_id', $collectionId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}

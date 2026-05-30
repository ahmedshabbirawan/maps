<?php

namespace App\Traits;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomAttributes
{
    public function attributeValues(): MorphMany
    {
        return $this->morphMany(AttributeValue::class, 'entity');
    }

    public function getCustomAttribute(string $slug): mixed
    {
        $attributeValue = $this->resolveAttributeValueBySlug($slug);

        return $attributeValue?->getCastedValue();
    }

    public function setCustomAttribute(string $slug, mixed $value): void
    {
        $attribute = $this->resolveAttributeDefinitionBySlug($slug);

        if (! $attribute) {
            return;
        }

        if ($value === null || $value === '') {
            $this->attributeValues()
                ->where('attribute_id', $attribute->id)
                ->delete();

            return;
        }

        $this->attributeValues()->updateOrCreate(
            ['attribute_id' => $attribute->id],
            ['value' => AttributeValue::serializeValue($attribute, $value)]
        );
    }

    public function getCustomAttributeRaw(string $slug): ?string
    {
        return $this->resolveAttributeValueBySlug($slug)?->value;
    }

    protected function resolveAttributeValueBySlug(string $slug): ?AttributeValue
    {
        if (! $this->relationLoaded('attributeValues')) {
            $this->load('attributeValues.attribute');
        }

        return $this->attributeValues->first(
            fn (AttributeValue $attributeValue) => $attributeValue->attribute?->slug === $slug
        );
    }

    protected function resolveAttributeDefinitionBySlug(string $slug): ?Attribute
    {
        if (method_exists($this, 'collection') && $this->collection) {
            return $this->collection->attributes()->where('slug', $slug)->first();
        }

        return null;
    }
}

<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Point;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PointAttributeFilter
{
    /**
     * @param  Builder<Point>|Relation  $query
     */
    public function apply(Builder|Relation $query, Collection $collection, Request $request): Builder|Relation
    {
        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }

        $filters = $this->normalizeFilters($request);

        foreach ($filters as $filter) {
            $attribute = $collection->attributes->firstWhere('id', $filter['attribute_id']);

            if (! $attribute) {
                continue;
            }

            $query->whereHas('attributeValues', function (Builder $valueQuery) use ($attribute, $filter) {
                $valueQuery
                    ->where('attribute_id', $attribute->id)
                    ->where('entity_type', Point::class);

                $this->applyOperator($valueQuery, $attribute->type, $filter['operator'], $filter['value']);
            });
        }

        return $query;
    }

    public function normalizeFilters(Request $request): array
    {
        if ($request->has('filters') && is_array($request->input('filters'))) {
            return collect($request->input('filters'))
                ->filter(fn ($f) => ! empty($f['attribute_id']))
                ->values()
                ->all();
        }

        if ($request->filled('attribute_id')) {
            return [[
                'attribute_id' => (int) $request->input('attribute_id'),
                'operator' => $request->input('operator', '='),
                'value' => $request->input('value'),
            ]];
        }

        return [];
    }

    protected function applyOperator(Builder $query, string $type, string $operator, mixed $value): void
    {
        $operator = in_array($operator, ['=', '!=', '>', '<', '>=', '<=', 'contains', 'between'], true)
            ? $operator
            : '=';

        match ($type) {
            'number' => $this->applyNumericFilter($query, $operator, $value),
            'date' => $this->applyDateFilter($query, $operator, $value),
            'boolean' => $query->where('value', filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0'),
            default => $this->applyStringFilter($query, $operator, $value),
        };
    }

    protected function applyNumericFilter(Builder $query, string $operator, mixed $value): void
    {
        $column = $this->numericCastExpression();

        match ($operator) {
            '>' => $query->whereRaw("{$column} > ?", [(float) $value]),
            '<' => $query->whereRaw("{$column} < ?", [(float) $value]),
            '>=' => $query->whereRaw("{$column} >= ?", [(float) $value]),
            '<=' => $query->whereRaw("{$column} <= ?", [(float) $value]),
            '!=' => $query->whereRaw("{$column} != ?", [(float) $value]),
            default => $query->whereRaw("{$column} = ?", [(float) $value]),
        };
    }

    protected function applyDateFilter(Builder $query, string $operator, mixed $value): void
    {
        $column = $this->dateCastExpression();

        if ($operator === 'between' && is_array($value)) {
            $start = $value['start'] ?? $value[0] ?? null;
            $end = $value['end'] ?? $value[1] ?? null;

            if ($start) {
                $query->whereRaw("{$column} >= ?", [$start]);
            }
            if ($end) {
                $query->whereRaw("{$column} <= ?", [$end]);
            }

            return;
        }

        match ($operator) {
            '>' => $query->whereRaw("{$column} > ?", [$value]),
            '<' => $query->whereRaw("{$column} < ?", [$value]),
            '>=' => $query->whereRaw("{$column} >= ?", [$value]),
            '<=' => $query->whereRaw("{$column} <= ?", [$value]),
            '!=' => $query->whereRaw("{$column} != ?", [$value]),
            default => $query->whereRaw("{$column} = ?", [$value]),
        };
    }

    protected function applyStringFilter(Builder $query, string $operator, mixed $value): void
    {
        if ($operator === 'contains') {
            $query->where('value', 'like', '%'.$value.'%');

            return;
        }

        if ($operator === '!=') {
            $query->where('value', '!=', (string) $value);

            return;
        }

        $query->where('value', '=', (string) $value);
    }

    protected function numericCastExpression(): string
    {
        return match (config('database.default')) {
            'pgsql' => 'CAST(value AS NUMERIC)',
            'sqlite' => 'CAST(value AS REAL)',
            default => 'CAST(value AS DECIMAL(20,8))',
        };
    }

    protected function dateCastExpression(): string
    {
        return match (config('database.default')) {
            'pgsql' => 'CAST(value AS DATE)',
            'sqlite' => 'DATE(value)',
            default => 'DATE(value)',
        };
    }
}

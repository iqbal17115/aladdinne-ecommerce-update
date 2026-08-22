<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
            if (method_exists($model, 'slugSourceValue')) {
                $sourceValue = $model->slugSourceValue();
            } else {
                $sourceColumn = method_exists($model, 'slugSourceColumn')
                    ? $model->slugSourceColumn()
                    : 'name';

                $sourceValue = $model->{$sourceColumn} ?? null;
            }

            if (! $sourceValue) {
                return;
            }

            $model->slug = $model->generateUniqueSlug($sourceValue, $model->getKey());
        });
    }

    public function generateUniqueSlug(string $value, $exceptId = null): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'item';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($exceptId, fn ($query) => $query->where($this->getKeyName(), '!=', $exceptId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}

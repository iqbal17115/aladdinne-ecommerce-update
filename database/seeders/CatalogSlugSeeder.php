<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CatalogSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->syncSlugs(Product::class, 'name');
        $this->syncSlugs(Category::class, 'name');
        $this->syncSlugs(SubCategory::class, 'name');
        $this->syncSlugs(Brand::class, 'name');
    }

    private function syncSlugs(string $modelClass, string $sourceColumn): void
    {
        /** @var Model $model */
        $model = new $modelClass;
        $usedSlugs = [];
        $updated = 0;
        $query = $modelClass::query()->withoutGlobalScopes();

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        $query
            ->select([$model->getKeyName(), 'slug', $sourceColumn])
            ->orderBy($model->getKeyName())
            ->chunkById(500, function ($records) use (&$usedSlugs, &$updated, $sourceColumn, $model) {
                foreach ($records as $record) {
                    $baseValue = $record->slug ?: $record->{$sourceColumn} ?: 'item';
                    $baseSlug = Str::slug($baseValue);

                    if ($baseSlug === '') {
                        $baseSlug = 'item';
                    }

                    $slug = $baseSlug;
                    $counter = 1;

                    while (isset($usedSlugs[$slug])) {
                        $slug = $baseSlug.'-'.$counter;
                        $counter++;
                    }

                    $usedSlugs[$slug] = true;

                    if ($record->slug !== $slug) {
                        $record->forceFill(['slug' => $slug])->saveQuietly();
                        $updated++;
                    }
                }
            }, $model->getKeyName());

        $this->command?->info(class_basename($modelClass).": {$updated} slug(s) updated.");
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $this->backfillUniqueSlugs('products', 'name');
        $this->backfillUniqueSlugs('brands', 'name');
        $this->backfillUniqueSlugs('categories', 'name');
        $this->backfillUniqueSlugs('sub_categories', 'name');

        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_slug_unique');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('brands_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropUnique('sub_categories_slug_unique');
        });
    }

    private function backfillUniqueSlugs(string $table, string $sourceColumn): void
    {
        $usedSlugs = [];

        DB::table($table)
            ->select('id', 'slug', $sourceColumn)
            ->orderBy('id')
            ->get()
            ->each(function ($row) use ($table, $sourceColumn, &$usedSlugs) {
                $baseValue = $row->slug ?: $row->{$sourceColumn} ?: 'item';
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

                DB::table($table)
                    ->where('id', $row->id)
                    ->update(['slug' => $slug]);
            });
    }
};

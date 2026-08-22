<?php

namespace Modules\Report\App\Repositories;

use App\Repositories\Repository;
use Modules\Report\App\Models\ProductSearchLog;

class ProductSearchLogRepository extends Repository
{
    public static function model()
    {
        return ProductSearchLog::class;
    }

    public static function updateOrCreateByRequest($search)
    {
        $searchLog = self::query()->where('keyword', $search)->first();
        if ($searchLog) {
            $searchLog->increment('total_search');
            return true;
        }
        $searchLog = self::create(['keyword' => $search]);
        return true;
    }
}

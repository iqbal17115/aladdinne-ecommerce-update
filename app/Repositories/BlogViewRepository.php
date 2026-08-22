<?php

namespace App\Repositories;

use App\Models\BlogView;

class BlogViewRepository extends Repository
{
    public static function model()
    {
        return BlogView::class;
    }
}

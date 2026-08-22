<?php

namespace App\Repositories;

use App\Models\LegalPage;

class LegalPageRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return LegalPage::class;
    }
}

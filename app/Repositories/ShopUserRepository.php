<?php

namespace App\Repositories;

use App\Models\ShopUser;

class ShopUserRepository extends Repository
{
    public static function model()
    {
        return ShopUser::class;
    }
}

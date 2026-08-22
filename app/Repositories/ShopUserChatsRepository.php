<?php

namespace App\Repositories;

use App\Models\ShopUserChats;

class ShopUserChatsRepository extends Repository
{
    public static function model()
    {
        return ShopUserChats::class;
    }
}

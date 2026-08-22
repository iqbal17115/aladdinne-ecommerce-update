<?php

namespace Modules\PreOrder\App\Models;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PreOrder\Database\Factories\DriverPreOrderFactory;

class DriverPreOrder extends Model
{
    use HasFactory;

     protected $guarded = ['id'];

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class, 'pre_order_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}

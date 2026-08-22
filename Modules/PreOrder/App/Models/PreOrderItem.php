<?php

namespace Modules\PreOrder\App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PreOrder\Database\Factories\PreOrderItemFactory;

class PreOrderItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function order()
    {
        return $this->belongsTo(PreOrder::class,'pre_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id')->withoutGlobalScopes();
    }

}

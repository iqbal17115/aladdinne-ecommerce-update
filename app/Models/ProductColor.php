<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductColor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

     public function thumbnail(): Attribute
    {
        $thumbnail = asset('default/default.jpg');
        if ($this->img && Storage::exists($this->img)) {
            $thumbnail = Storage::url($this->img);
        }
        return new Attribute(
            get: fn() => $thumbnail
        );
    }
}

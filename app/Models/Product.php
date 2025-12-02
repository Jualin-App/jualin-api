<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'seller_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'image',
        'category',
        'condition',
        'status',
    ];
    protected $casts = [
        'price' => 'integer',
        'stock_quantity' => 'integer',
    ];
}

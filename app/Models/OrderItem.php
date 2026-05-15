<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sku',        // был product_id
        'offer_id',   // новое
        'product_name',
        'category_id',
        'quantity',
        'price',
        'category_lookup_failed',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'category_lookup_failed' => 'boolean',
    ];

    // связь с заказом
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

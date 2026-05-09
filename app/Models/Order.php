<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'posting_number',
        'order_id',
        'customer_name',
        'customer_phone',
        'status',
        'delivery_date',
        'chat_id',
        'user_id',
        'order_type',
        'api_account_id',
        'payment_date',
    ];

    protected $casts = [
        'delivery_date' => 'datetime',
    ];

    // связи
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messageLogs()
    {
        return $this->hasMany(MessageLog::class);
    }

    public function isFbs(): bool
    {
        return $this->order_type === 'fbs';
    }

    public function isFbo(): bool
    {
        return $this->order_type === 'fbo';
    }

    public function apiAccount()
    {
        return $this->belongsTo(ApiAccount::class);
    }
}

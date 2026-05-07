<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'order_id',
        'recipient_name',
        'sent_at',
        'status',
        'error_text',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // связи
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

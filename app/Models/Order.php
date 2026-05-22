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
        'payment_date'  => 'datetime',
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


    public function getLastMessageLogAttribute()
    {
        return $this->messageLogs()->latest()->first();
    }

    public function getPaymentDaysDiffAttribute()
    {
        if (!$this->payment_date) {
            return null;
        }
        return $this->payment_date->diffForHumans(); // например, "3 дня назад"
    }

    public function getPaymentDaysAttribute(): ?int
    {
        if (!$this->payment_date) {
            return null;
        }
        return $this->payment_date->diffInDays(now());
    }

    public function getPaymentIntervalAttribute(): ?string
    {
        if (!$this->payment_date) {
            return null;
        }
        $diff = now()->diff($this->payment_date);
        $days = $diff->d;
        $hours = $diff->h;

        if ($days == 0 && $hours == 0) {
            return 'менее часа';
        }

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' ' . $this->pluralForm($days, 'день', 'дня', 'дней');
        }
        if ($hours > 0) {
            $parts[] = $hours . ' ' . $this->pluralForm($hours, 'час', 'часа', 'часов');
        }
        return implode(' ', $parts);
    }

    private function pluralForm($number, $one, $two, $many): string
    {
        $mod10 = $number % 10;
        $mod100 = $number % 100;
        if ($mod100 >= 11 && $mod100 <= 19) {
            return $many;
        }
        if ($mod10 == 1) {
            return $one;
        }
        if ($mod10 >= 2 && $mod10 <= 4) {
            return $two;
        }
        return $many;
    }


}

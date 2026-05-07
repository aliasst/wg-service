<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ApiAccount extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'client_id', 'api_key', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Шифрование api_key при сохранении
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = Crypt::encryptString($value);
    }

    // Дешифрование при получении
    public function getApiKeyAttribute($value)
    {
        return Crypt::decryptString($value);
    }
}

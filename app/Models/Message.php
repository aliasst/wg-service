<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_mapping_id',
        'header',
        'body',
        'user_id',
    ];

    // связи
    // Связь с категорией (CategoryMapping)
    public function categoryMapping()
    {
        return $this->belongsTo(CategoryMapping::class, 'category_mapping_id');
    }

    // Связь с пользователем (кто отправил)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Связь с логами отправки
    public function logs()
    {
        return $this->hasMany(MessageLog::class);
    }
}

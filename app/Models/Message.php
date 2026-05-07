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
    public function category()
    {
        return $this->belongsTo(CategoryMapping::class, 'category_mapping_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(MessageLog::class);
    }
}

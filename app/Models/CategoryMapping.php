<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_name',
        'ozon_category_ids',
        'is_active',
    ];

    protected $casts = [
        'ozon_category_ids' => 'array',  // автоматически преобразует JSON в массив
        'is_active' => 'boolean',
    ];

    // связь с сообщениями (шаблонами)
    public function messages()
    {
        return $this->hasMany(Message::class, 'category_mapping_id');
    }
}

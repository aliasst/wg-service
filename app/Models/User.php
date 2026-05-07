<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'login',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Вспомогательный метод
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Связи (оставляем)
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'user_id');
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $login = env('WG_ADMIN_LOGIN', 'wg_super_admin');
        $password = env('WG_ADMIN_PASSWORD', 'password');

        if (!User::where('login', $login)->exists()) {
            User::create([
                'login' => $login,
                'password' => Hash::make($password),
                'role' => 'admin',
            ]);
        }
    }

}

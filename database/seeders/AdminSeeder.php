<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Super',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin1234'), // mot de passe initial
            'role_id' => 1, // admin
            'status' => 'approved',
            'must_change_password' => false,
            'has_logged_in' => true,
            'password_expires_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Admin créé : admin@example.com / admin1234');
    }
}
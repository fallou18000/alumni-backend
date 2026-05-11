<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
    public function model(array $row)
    {
        $user = User::create([
            'first_name' => $row[0],
            'last_name'  => $row[1],
            'email'      => $row[2],
            'password'   => null,
            'status'     => 'pending',
        ]);

        // 🔥 ENVOI DU LIEN RESET PASSWORD
        Password::sendResetLink([
            'email' => $user->email
        ]);

        return $user;
    }
}
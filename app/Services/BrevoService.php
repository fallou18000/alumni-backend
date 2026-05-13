<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BrevoService
{
    public static function sendMail($to, $subject, $html)
    {
        return Http::withHeaders([
            'api-key' => env('BREVO_KEY'),
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            "sender" => [
                "name" => env('MAIL_FROM_NAME', 'Alumni System'),
                "email" => env('MAIL_FROM_ADDRESS', 'no-reply@test.com')
            ],
            "to" => [
                [
                    "email" => $to
                ]
            ],
            "subject" => $subject,
            "htmlContent" => $html
        ]);
    }
}
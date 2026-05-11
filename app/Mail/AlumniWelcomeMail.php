<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlumniWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

   public function __construct(
    public $user,
    public string $url
) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur la plateforme Alumni'
        );
    }

  public function content(): Content
{
    return new Content(
        view: 'emails.alumni_welcome',
        with: [
            'user' => $this->user,
            'url' => $this->url
        ]
    );
}
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Pemberitahuan Reset Password')
            ->greeting('Halo!')
            ->line('Anda menerima email ini karena kami mendapatkan permintaan reset password untuk akun Anda.')
            ->action('Reset Password', $url)
            ->line('Tautan reset password ini akan hangus dalam 60 menit.')
            ->line('Jika Anda tidak merasa meminta reset password, Anda tidak perlu melakukan tindakan apapun dan abaikan email ini.')
            ->salutation('Salam hormat, Algrow Capital');
    }
}

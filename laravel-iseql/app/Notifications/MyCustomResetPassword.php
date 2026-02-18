<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class MyCustomResetPassword extends ResetPasswordNotification
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $link = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Password Reset Request - Glucose Analysis')
            // Usiamo 'view' per caricare un file blade personalizzato
            ->view('emails.passwordReset', [
                'name' => $notifiable->name,
                'link' => $link
            ]);
    }}

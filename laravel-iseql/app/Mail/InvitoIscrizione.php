<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class InvitoIscrizione extends Mailable
{
    use Queueable, SerializesModels;

    public $patient;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */


    public function __construct(Patient $patient, User $user, $link)
    {
        $this->patient = $patient;
        $this->user = $user;
        $this->link = $link;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Ottieni il contenuto della vista come stringa
        $htmlContent = view('emails.invitoIscrizione', [
            'clienteName' => $this->patient->name,
            'clienteSurname' => $this->patient->surname,
            'userName' => $this->user->name,
            'userSurname' => $this->user->surname,
            'link' => $this->link,
        ])->render();  // render() restituisce il contenuto HTML della vista

        return $this->html($htmlContent)
            ->subject('Invito ad iscriverti a ISEQL-Glucose Analyzer');
    }
}

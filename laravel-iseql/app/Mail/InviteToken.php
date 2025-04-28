<?php
namespace App\Mail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteToken extends Model
{
    use HasFactory;

    // Imposta la tabella associata (nel caso in cui il nome della tabella non fosse il plurale del nome del modello)
    protected $table = 'invite_tokens';

    // Definisce gli attributi che possono essere assegnati in massa
    protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];

    // Indica che il modello non usa i timestamp (se Laravel non gestisce automaticamente i timestamp)
    public $timestamps = true;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteToken extends Model
{
    // Questi sono i campi che permetti di salvare
    protected $fillable = ['email', 'token', 'expires_at'];
}

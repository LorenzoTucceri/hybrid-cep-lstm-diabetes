<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'message', 'status'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }


    public function users()
    {
        return $this->belongsTo(User::class);
    }
}

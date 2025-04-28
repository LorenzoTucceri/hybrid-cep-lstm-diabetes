<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject as AuthenticatableUserContract;

class Patient extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'surname',
        'email',
        'birth',
        'address',
        'telephone_number',
        'doctor_id',
        'gender'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function latestFile()
    {
        return $this->hasOne(File::class)->latestOfMany();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id'); // patient_id in Patient, id in User
    }
    public function doctor()
    {
        return $this->belongsTo(User::class);
    }

}





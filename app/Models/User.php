<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'status',
        'last_login_at',
        'last_access',
        'verification_code',
    ];
    

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_access' => 'datetime',
    ];

    public function verifyCode($plainCode)
    {
        return Hash::check($plainCode, $this->verification_code);
    }

    public function markEmailAsVerified()
    {
        $this->update([
            'email_verified_at' => now(),
            'verification_code' => null,
        ]);
    }
}
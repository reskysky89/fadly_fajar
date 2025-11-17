<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    // KITA BERI TAHU LARAVEL BAHWA PRIMARY KEY KITA BUKAN 'id'
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     * 
     */
    protected $fillable = [
        'nama', // Sebelumnya 'name'
        'username', // Kita tambahkan
        'email',
        'password',
        'role_user', // Kita tambahkan
        'kontak', // Kita tambahkan
        'alamat', // Kita tambahkan
        'foto_profil', // Kita tambahkan
        'status_akun', // Kita tambahkan
        'last_login', // Kita tambahkan
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
        ];
    }
}

<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use  HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'IMG',"coverIMG","adSoyad","kullaniciAdi", 'email', 'password',
    ];
    protected $primaryKey = "ID";
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = "tb_kullanicilar";
    protected $hidden = [
        'kullaniciParola', 'remember_token',
    ];

}

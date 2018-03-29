<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $primaryKey = "ID";
    protected $table = "tb_sohbetler";
    protected $fillable = ['yayinID', 'kullaniciID', 'mesajAciklamasi'];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $primaryKey = "ID";
    protected $table = "tb_paylasimlar";
    protected $fillable = ['paylasim_tipi', 'kullanici_id', 'durum', 'resim', 'paylasilan_gonderi'];
}

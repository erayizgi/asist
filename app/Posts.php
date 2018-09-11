<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $primaryKey = "paylasim_id";
    protected $table = "tb_paylasimlar";
    protected $fillable = ['paylasim_tipi', 'kullanici_id', 'durum', 'resim', 'paylasilan_gonderi'];

    public static function selectable()
    {
        return ['paylasim_tipi', 'kullanici_id', 'durum', 'resim', 'paylasilan_gonderi',"created_at","updated_at"];
    }
}

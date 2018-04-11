<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    protected $primaryKey = "begeni_id";
    protected $table = "tb_begeni";
    protected $fillable = ['begenen_id', 'paylasim_id', 'begenilen_tarih'];
    public $timestamps = false;

}

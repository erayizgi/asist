<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activities extends Model
{
    protected $table = "islemler";
    protected $primaryKey = "rec_id";
    public $timestamps = false;
    protected $fillable = ["kullanici_id", "islem_turu", "islem_id", "islem_tarihi"];
}

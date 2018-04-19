<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForecastSurveys extends Model
{
    protected $primaryKey = "anket_id";
    protected $table = "tb_iddaa_anketleri";
    protected $fillable = ['yanit_id', 'kullanici_id', 'mac_id'];
}

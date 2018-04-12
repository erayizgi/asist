<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    protected $primaryKey = "tahmin_id";
    protected $table = "tb_iddaa_tahminleri";
    protected $fillable = ['tahminci_id', 'tahmin_yorumu', 'mac_id'];

}

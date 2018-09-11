<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Games extends Model
{
    protected $table = "tb_maclar";
    protected $primaryKey = "mac_id";
    protected $fillable = ["evsahibi","deplasman","mac_tarihi","bet","oran","kupon_id","durum","odd_option_id"];
    //
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = "tb_kuponlar";
    protected $primaryKey = "kupon_id";
    protected $fillable = ["kupon_sahibi","kupon_sonucu","misli","sistem","kazanc","kesinKazanc"];
    protected $hidden = ["betnano_id"];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $primaryKey = "ID";
    protected $table = "tb_takip";
    protected $fillable = ['takipEdenID', 'takipEdilenID', 'olusturulmaTarihi'];

    public static function selectable()
    {
        return ['takipEdenID', 'takipEdilenID', 'olusturulmaTarihi'];
    }

}

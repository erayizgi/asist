<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follow extends Model
{
	use SoftDeletes;
    protected $primaryKey = "ID";
    protected $table = "tb_takip";
    protected $fillable = ['takipEdenID', 'takipEdilenID', 'olusturulmaTarihi'];

    public static function selectable()
    {
        return ['takipEdenID', 'takipEdilenID', 'olusturulmaTarihi'];
    }

}

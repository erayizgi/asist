<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Comments extends Model
{
    use SoftDeletes;
    protected $table = "tb_paylasim_yorumlari";
    protected $primaryKey = "yorum_id";
    protected $dates = ['deleted_at'];
    protected $fillable = ['kullanici_id', 'paylasim_id', 'icerik_tipi', 'yorum'];
}

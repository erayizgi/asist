<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $primaryKey = "bildirim_id";
    protected $table = "bildirimler";
    protected $fillable = ['alici_id', 'bildirim_tipi', 'bildirim_url', 'olusturan_id', 'olusturulma_tarihi','okundu'];
}

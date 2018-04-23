<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Conversation extends Model
{
    use SoftDeletes;
    protected $table = "tb_conversation";
    protected $primaryKey = "conversation_id";
    protected $dates = ['deleted_at'];
    protected $fillable = ['sender_id', 'receiver_id'];
    protected $appends = ["sender"];

    public function getSenderAttribute()
    {
        $roles = DB::table('tb_kullanicilar')
            ->where('ID',$this->attributes['sender_id'])
            ->select('tb_kullanicilar.IMG','tb_kullanicilar.adSoyad','tb_kullanicilar.IMG','tb_kullanicilar.kullaniciAdi')->first();
        return  $roles;
    }
}

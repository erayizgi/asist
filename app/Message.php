<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    protected $table = "tb_message";
    protected $primaryKey = "message_id";

    protected $fillable = ['conversation_id', 'user_id', 'content'];
	protected $appends = ["sender"];

	public function getSenderAttribute()
	{
		$roles = DB::table('tb_kullanicilar')
			->where('ID',$this->attributes['user_id'])
			->select('tb_kullanicilar.IMG','tb_kullanicilar.adSoyad','tb_kullanicilar.IMG','tb_kullanicilar.kullaniciAdi')->first();
		return  $roles;
	}
}

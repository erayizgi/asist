<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Comments extends Model
{
	use SoftDeletes;
	protected $table = "tb_paylasim_yorumlari";
	protected $primaryKey = "yorum_id";
	protected $dates = ['deleted_at'];
	protected $fillable = ['kullanici_id', 'ust_id' , 'paylasim_id', 'icerik_tipi', 'yorum'];
	public $appends = ["yorum_yanit"];

	public function getYorumYanitAttribute()
	{
		if(isset($this->attributes["yorum_id"])){
			return DB::table("tb_paylasim_yorumlari")->select(
				'tb_paylasim_yorumlari.*',
				'tb_paylasim_yorumlari.created_at as yorum_yapilan_tarih',
				'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
				->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasim_yorumlari.kullanici_id")
				->where("ust_id", $this->attributes["yorum_id"])->get();
		}else{
			return [];
		}

	}
}

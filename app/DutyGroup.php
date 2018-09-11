<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DutyGroup extends Model
{
	use SoftDeletes;
	protected $primaryKey = "grup_id";
	protected $table = "gorev_gruplari";
	protected $guarded = [];
	protected $appends = ["katilimci"];
	public function getOnkosulluGrupAttribute()
	{
		if ($this->attributes["onkosullu_grup"] != null) {
			return DB::table("gorev_gruplari")->where("grup_id", $this->attributes["onkosullu_grup"])->first();
		} else {
			return null;
		}
	}

	public function duties()
	{
		return $this->hasMany("App\Duty","grup_id","grup_id");
	}

	public function getKatilimciAttribute()
	{
		$dc = DB::table("kullanici_gorevleri")->select("kullanici_id")
			->where("grup_id",$this->attributes["grup_id"])->groupBy("kullanici_id")->get();
		return $dc->count();
	}
}

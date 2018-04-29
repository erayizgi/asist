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

	public function getOnkosulluGrupAttribute()
	{
		if($this->attributes["onkosullu_grup"] != null){
			return DB::table("gorev_gruplari")->where("grup_id",$this->attributes["onkosullu_grup"])->first();
		}else{
			return null;
		}
	}
}

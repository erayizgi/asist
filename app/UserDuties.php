<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDuties extends Model
{
	use SoftDeletes;
	protected $primaryKey = "kg_id";
	protected $table = "kullanici_gorevleri";
	protected $guarded = [];

	public function duty()
	{
		return $this->hasOne("App\Duty","gorev_id","gorev_id");
	}
}

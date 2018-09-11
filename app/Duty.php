<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Duty extends Model
{
	use SoftDeletes;
	protected $primaryKey = "gorev_id";
	protected $table = "gorevler";
	protected $guarded = [];
	public function dutyGroup()
	{
		return $this->hasOne("App\DutyGroup","grup_id","grup_id");
	}


}

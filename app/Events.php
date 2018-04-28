<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Events extends Model
{
	protected $table = "events";
	protected $primaryKey = "event_id";
	protected $fillable = [
		"event_id",
		"event_oid",
		"type",
		"start_date",
		"country",
		"league_name",
		"league_code",
		"mbc",
		"home",
		"away",
		"identifier_id"
	];

	protected $appends = ['odds'];

	public function getOddsAttribute()
	{
		$odds = DB::table('odd_options')
			->select('odd_options.odd_type_id')
			->join('odd_types', 'odd_types.odd_type_id', 'odd_options.odd_type_id')
			->where('odd_options.event_id', $this->attributes['event_id'])
			->groupBy('odd_options.odd_type_id')->get();

		return $odds;
	}
}

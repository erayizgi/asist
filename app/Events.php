<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Events extends Model
{
    protected $primaryKey = "event_id";
    protected $table = "events";

    protected $fillable = [
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

    public function getOdssAtribute(){


        //SELECT odd_options.odd_type_id FROM odd_options
        //INNER JOIN odd_types ON odd_types.odd_type_id = odd_options.odd_type_id
        //WHERE odd_options.event_id = 14449
        //GROUP BY odd_options.odd_type_id
        //

        return DB::table('odd_options')
            ->select('odd_options.odd_type_id')
            ->join('odd_types', 'odd_types.odd_type_id', 'odd_options_odd_type_id')
            ->where('odd_options.event_id', $this->attributes['event_id'])
            ->groupBy('odd_options.odd_type_id')->count();

        //$this->attributes['event_id'];
    }
}

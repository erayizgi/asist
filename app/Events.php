<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}

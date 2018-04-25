<?php

namespace App\Http\Controllers;


use DB;
use DateTime;
use Exception;
use App\Events;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use App\Libraries\TReq;
use App\Libraries\Res;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class CouponController extends Controller
{
    public function football(){

        try{
            $client    = new \GuzzleHttp\Client();

            $response = $client->request('GET', 'https://www.tuttur.com/draw/events/type/football');
            $response = json_decode($response->getBody());

            $r = $response->events;

            $result = array("message" => "");
            foreach ($r as $key => $val) {
                if ($r->$key->code >= 100) {

                    $event = array(
                        "event_oid"     => $r->$key->code,
                        "type"          => $r->$key->type,
                        "start_date"    => $r->$key->startDate,
                        "country"       => $r->$key->country,
                        "league_name"   => $r->$key->leagueName,
                        "league_code"   => $r->$key->leagueCode,
                        "mbc"           => $r->$key->mbc,
                        "home"          => $r->$key->homeTeamName,
                        "away"          => $r->$key->awayTeamName,
                        "identifier_id" => $r->$key->identifier
                    );

                    $check = Events::where('identifier_id', $r->$key->identifier)->count();

                    if($check == 0){
                        if ($event_id = Events::create($event)->event_id) {
                            $odds = $r->$key->odds;
                            $result = ["message" => $result["message"] . "\n" . $event_id . " ID li Maç Eklendi"];
                            foreach ($odds as $k => $v) {

                                if ($k != "SC.GG" && $k != "SC.NG" && $k != "F.E" && $k != "F.O" && $k != "UNDER" && $k != "OVER") {
                                    $type = explode(".", $k);
                                    $opt = $type[1];
                                    $type = $type[0];

                                    $oddType = DB::table('odd_types')->where('odd_type_code', $type);

                                    if ($oddType) {
                                        $oddType = $oddType->first();
                                        if ($oddType->odd_type_code == "GS") {
                                            if ($opt[1] == "p") {
                                                $option = $opt[0] . "+" . " Gol";
                                            } else {
                                                $option = $opt[0] . "-" . $opt[1] . " Gol";
                                            }
                                        } elseif ($oddType->odd_type_code == "SC") {
                                            $option = $opt[0] . ":" . $opt[1];
                                        } elseif ($oddType->odd_type_code == "F15") {
                                            if ($opt == "O") {
                                                $option = "1,5 Üstü";
                                            } else {
                                                $option = "1,5 Altı";
                                            }
                                        } elseif ($oddType->odd_type_code == "F35") {
                                            if ($opt == "O") {
                                                $option = "3,5 Üstü";
                                            } else {
                                                $option = "3,5 Altı";
                                            }
                                        } elseif ($oddType->odd_type_code == "H15") {
                                            if ($opt == "O") {
                                                $option = "Üstü";
                                            } else {
                                                $option = "Altı";
                                            }
                                        } else {
                                            $option = $opt;
                                        }

                                        $odd_option = array(
                                            "odd_option"  => $option,
                                            "odd"         => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id"    => $event_id
                                        );


                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status"  => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " " . $option . " Seçeneği Eklendi"
                                            );
                                        }

                                    } else {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => "Bahis Tipi Bulunamadı"
                                        );
                                    }

                                }

                                if ($k == "SC.GG" || $k == "SC.NG") {
                                    $oddType = DB::table('odd_types')->where('odd_type_code', 'SC.GG');
                                    if ($oddType) {
                                        $oddType = $oddType->first();
                                        if ($k == "SC.GG") {
                                            // VAR
                                            $odd_option = array(
                                                "odd_option"  => "Var",
                                                "odd"         => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id"    => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status"  => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " VAR Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                        if ($k == "SC.NG") {
                                            // YOK
                                            $odd_option = array(
                                                "odd_option"  => "Yok",
                                                "odd"         => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id"    => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status"  => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " YOK Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                    } else {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => "Bahis Tipi Bulunamadı"
                                        );
                                    }
                                }


                                if ($k == "F.E" || $k == "F.O") {
                                    $oddType = DB::table('odd_types')->where('odd_type_code', 'F.E');
                                    if ($oddType) {
                                        $oddType = $oddType->first();
                                        if ($k == "F.E") {
                                            // ÇİFT
                                            $odd_option = array(
                                                "odd_option"  => "Çift",
                                                "odd"         => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id"    => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status"  => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Çift Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                        if ($k == "F.O") {
                                            // TEK
                                            $odd_option = array(
                                                "odd_option"  => "Tek",
                                                "odd"         => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id"    => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status"  => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Tek Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                    } else {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => "Bahis Tipi Bulunamadı"
                                        );
                                    }
                                }



                                if ($k == "UNDER" || $k == "OVER") {

                                    $oddType = DB::table('odd_types')->where('odd_type_code', 'OVER');

                                    if ($oddType) {
                                        $oddType = $oddType->first();
                                        if ($k == "UNDER") {
                                            // VAR
                                            $odd_option = array(
                                                "odd_option"  => "Altı",
                                                "odd"         => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id"    => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status"  => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                        if ($k == "OVER") {
                                            // YOK
                                            $odd_option = array(
                                                "odd_option"  => "Üstü",
                                                "odd"         => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id"    => $event_id
                                            );

                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status"  => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Üstü Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => "OVER Sorgusu Çalışmadı!"
                                        );
                                    }

                                }

                            }
                        } else {
                            $result = array(
                                "status"  => FALSE,
                                "message" => $event["home"] . " - " . $event["away"] . " Maçı sistemde bulunmaktadır"
                            );
                        }
                    }

                } else {
                    $result = array(
                        "status"  => FALSE,
                        "message" => $result["message"] . "\n" . " Maç Uzun Vadeli"
                    );
                }
            }
            return json_encode($result);


        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }

    }

    public function basketball()
    {
        $client    = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://www.tuttur.com/draw/events/type/basketball');
        $response = json_decode($response->getBody());

        $r = $response->events;

        $result = array("message" => "");
        foreach ($r as $key => $val) {
            if ($r->$key->code >= 100) {
                $lmt = "*LIMIT*";
                $event = array(
                    "event_oid"     => $r->$key->code,
                    "type"          => $r->$key->type,
                    "start_date"    => $r->$key->startDate,
                    "country"       => $r->$key->country,
                    "league_name"   => $r->$key->leagueName,
                    "league_code"   => $r->$key->leagueCode,
                    "mbc"           => $r->$key->mbc,
                    "home"          => $r->$key->homeTeamName,
                    "away"          => $r->$key->awayTeamName,
                    "identifier_id" => $r->$key->identifier,
                    "s1Handicap"    => $r->$key->s1Handicap,
                    "f1Handicap"    => $r->$key->extraHomeHandicap,
                    "totalLimit"    => $r->$key->odds->$lmt
                );

                $check = Events::where('identifier_id', $r->$key->identifier)->count();
                if($check == 0){
                    if ($event_id = Events::create($event)->event_id) {
                        $odds = $r->$key->odds;
                        $result = array("message" => $result["message"] . "\n" . $event_id . " ID li Maç Eklendi");
                        foreach ($odds as $k => $v) {

                            if ($k != "SC.GG" && $k != "SC.NG" && $k != "F.E" && $k != "F.O" && $k != "UNDER" && $k != "OVER" && $k != "F1" && $k != "F2" && $k != "S1" && $k != "S2") {
                                $type = explode(".", $k);
                                $opt = $type[1];
                                $type = $type[0];
                                $oddType = DB::table('odd_types')->where('odd_type_code', $type);
                                if ($oddType) {
                                    $oddType = $oddType->row();
                                    if ($oddType->odd_type_code == "GS") {
                                        if ($opt[1] == "p") {
                                            $option = $opt[0] . "+" . " Gol";
                                        } else {
                                            $option = $opt[0] . "-" . $opt[1] . " Gol";
                                        }
                                    } elseif ($oddType->odd_type_code == "SC") {
                                        $option = $opt[0] . ":" . $opt[1];
                                    } elseif ($oddType->odd_type_code == "F15") {
                                        if ($opt == "O") {
                                            $option = "1,5 Üstü";
                                        } else {
                                            $option = "1,5 Altı";
                                        }
                                    } elseif ($oddType->odd_type_code == "F35") {
                                        if ($opt == "O") {
                                            $option = "3,5 Üstü";
                                        } else {
                                            $option = "3,5 Altı";
                                        }
                                    } elseif ($oddType->odd_type_code == "H15") {
                                        if ($opt == "O") {
                                            $option = "Üstü";
                                        } else {
                                            $option = "Altı";
                                        }
                                    } else {
                                        $option = $opt;
                                    }
                                    $odd_option = array(
                                        "odd_option"  => $option,
                                        "odd"         => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id"    => $event_id
                                    );
                                    if (DB::table('odd_options')->insert_odds($odd_option)) {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " " . $option . " Seçeneği Eklendi"
                                        );
                                    }
                                } else {
                                    $result = array(
                                        "status"  => FALSE,
                                        "message" => "Bahis Tipi Bulunamadı"
                                    );
                                }
                            }
                            if ($k == "SC.GG" || $k == "SC.NG") {
                                $oddType = DB::table('odd_types')->where('odd_type_code', 'SC.GG');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    if ($k == "SC.GG") {
                                        // VAR
                                        $odd_option = array(
                                            "odd_option"  => "Var",
                                            "odd"         => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id"    => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status"  => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " VAR Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                    if ($k == "SC.NG") {
                                        // YOK
                                        $odd_option = array(
                                            "odd_option"  => "Yok",
                                            "odd"         => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id"    => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status"  => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " YOK Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                } else {
                                    $result = array(
                                        "status"  => FALSE,
                                        "message" => "Bahis Tipi Bulunamadı"
                                    );
                                }
                            }
                            if ($k == "F.E" || $k == "F.O") {
                                $oddType = DB::table('odd_types')->where('odd_type_code', 'F.E');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    if ($k == "F.E") {
                                        // ÇİFT
                                        $odd_option = array(
                                            "odd_option"  => "Çift",
                                            "odd"         => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id"    => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status"  => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Çift Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                    if ($k == "F.O") {
                                        // TEK
                                        $odd_option = array(
                                            "odd_option"  => "Tek",
                                            "odd"         => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id"    => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status"  => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Tek Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                } else {
                                    $result = array(
                                        "status"  => FALSE,
                                        "message" => "Bahis Tipi Bulunamadı"
                                    );
                                }
                            }
                            if ($k == "F1") {
                                $oddType = DB::table('odd_types')->where('odd_type_code', 'F');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    // VAR
                                    $odd_option = array(
                                        "odd_option"  => "1 (" . $r->$key->extraHomeHandicap . ")",
                                        "odd"         => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id"    => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                        );
                                    }
                                }
                            }
                            if ($k == "F2") {
                                $oddType = DB::table('odd_types')->where('odd_type_code', 'F');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    // VAR
                                    $odd_option = array(
                                        "odd_option"  => "2 (" . ($r->$key->extraHomeHandicap * -1) . ")",
                                        "odd"         => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id"    => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                        );
                                    }
                                }
                            }
                            if ($k == "S1") {
                                $oddType = DB::table('odd_types')->where('odd_type_code', 'F');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    // VAR
                                    $odd_option = array(
                                        "odd_option"  => "1 (" . ($r->$key->s1Handicap) . ")",
                                        "odd"         => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id"    => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                        );
                                    }
                                }
                            }
                            if ($k == "S2") {
                                $oddType = DB::table('odd_types')->where('odd_type_code', 'F');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    // VAR
                                    $odd_option = array(
                                        "odd_option"  => "2 (" . ($r->$key->s1Handicap * -1) . ")",
                                        "odd"         => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id"    => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status"  => FALSE,
                                            "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                        );
                                    }
                                }
                            }
                            if ($k == "UNDER" || $k == "OVER") {
                                $oddType =  DB::table('odd_types')->where('odd_type_code', 'OVER');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    if ($k == "UNDER") {
                                        // VAR
                                        $odd_option = array(
                                            "odd_option"  => $r->$key->odds->$lmt . " Altı",
                                            "odd"         => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id"    => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status"  => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                    if ($k == "OVER") {
                                        // YOK
                                        $odd_option = array(
                                            "odd_option"  => $r->$key->odds->$lmt . " Üstü",
                                            "odd"         => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id"    => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status"  => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Üstü Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $result = array(
                            "status"  => FALSE,
                            "message" => $event["home"] . " - " . $event["away"] . " Maçı sistemde bulunmaktadır"
                        );
                    }
                }
            } else {
                $result = array(
                    "status"  => FALSE,
                    "message" => $result["message"] . "\n" . " Maç Uzun Vadeli"
                );
            }
        }
        echo json_encode($result);
    }

    public function events(Request $request){

        try{
            $now      = new DateTime();
            $today    = $now->getTimestamp();
            $tomorrow = $now->modify('+1 Day')->getTimestamp();

            $query = TReq::multiple($request, Events::class);
            $data  = $query['query']->where('start_date', '>=', $today)->where('start_date', '<', $tomorrow)->get();

            $result = [
                'metadata'=> [
                    'count' => $data->count(),
                    'offset'=> $query['offset'],
                    'limit' => $query['limit'],
                 ], 'data'=> $data
            ];

            return Res::success(200,'Maçlar',$result);

        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function odds($event_id){
        try{
            $now = new DateTime();

            $events = Events::where([
                'event_id' => $event_id,
                'start_date' => $now->getTimestamp(),
            ]);

            if(!$events->count() > 0){
                //SELECT `odd_type`, `odd_options`.`odd_type_id` FROM `odd_options` LEFT JOIN `odd_types` ON `odd_types`.`odd_type_id` = `odd_options`.`odd_type_id` WHERE `event_id` = '589' GROUP BY `odd_type` ORDER BY `odd_type_id` ASC

                return DB::table('odd_options')->select('odd_types.odd_type','odd_options.odd_type_id')
                    ->join('odd_types', function($join) {
                        $join->on('odd_types.odd_type_id', '=', 'odd_options.odd_type_id');
                    })->where('event_id', '=', 589)
                    ->orderBy('odd_type_id', 'ASC')
                    ->groupBy('odd_type')
                    ->get();

                /*
                $odd_group = DB::table('odd_options')->select('odd_types.odd_type', 'odd_options.odd_type_id')
                    ->join('odd_types', 'odd_types.odd_type_id', 'odd_options.odd_type_id', 'left')
                    ->where('event_id', $event_id)
                    ->groupBy('odd_types.odd_type')
                    ->orderBy('odd_types.odd_type_id', 'ASC')->get();

                foreach($odd_group as $k => $v){
                    $odd_group[$k]['options'] = DB::table('odd_options')->select('odd_option', 'odd', 'odd_option_id')
                        ->join('odd_types', 'odd_types.odd_type_id', 'odd_options.odd_type_id', 'left')
                        ->where('event_id', $event_id)
                        ->where('odd_options.odd_type_id', $odd_group[$k]['odd_type_id'])->get();

                }

                return $odd_group;
                */
            }

            $result = [
                'data'=> Events::where([
                    'event_id' => $event_id,
                    'start_date' => $now->getTimestamp(),
                ])
            ];

            return Res::success(200,'Maçlar',$result);

        }catch(exception $e){
            return Res::fail($e->getCode(), $e->getMessage());
        }

        //$event = $this->db->get_where("events",array("start_date>="=>$dt,"event_id" => $mac_id));
        //		if($event->num_rows() >0){
        //			$this->db->select("odd_type,odd_options.odd_type_id");
        //			$this->db->join("odd_types","odd_types.odd_type_id = odd_options.odd_type_id","left");
        //			$this->db->where("event_id",$mac_id);
        //			$this->db->group_by("odd_type");
        //			$this->db->order_by("odd_type_id","ASC");
        //			$odd_group = $this->db->get("odd_options")->result_array();
        //			foreach($odd_group as $key=>$val){
        //				$this->db->select("odd_option,odd,odd_option_id");
        //				$this->db->join("odd_types","odd_types.odd_type_id = odd_options.odd_type_id","left");
        //				$this->db->where("event_id",$mac_id);
        //				$this->db->where("odd_options.odd_type_id",$odd_group[$key]["odd_type_id"]);
        //				$odd_group[$key]["options"] = $this->db->get("odd_options")->result_array();
        //			}
        //			return $odd_group;
        //		}else{
        //			return FALSE;
        //		}
    }


    //public function get_oranlar($mac_id)
    //	{
    //		$now = new DateTime();
    //		$odds = $this->Kupon_model->get_oranlar($now->getTimestamp(), $mac_id);
    //		if (count($odds) > 0) {
    //			$result = array(
    //				"status" => TRUE,
    //				"data"   => $odds
    //			);
    //		} else {
    //			$result = array(
    //				"status"  => FALSE,
    //				"message" => "Bu maça bahisler kapanmıştır"
    //			);
    //		}
    //		$this->output->set_content_type('application/json')->set_output(json_encode($result));
    //	}

    //

}

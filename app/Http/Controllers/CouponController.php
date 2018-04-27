<?php

namespace App\Http\Controllers;


use App\Posts;
use DB;
use DateTime;
use Exception;
use App\Events;
use App\Coupon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use App\Libraries\TReq;
use App\Libraries\Res;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function football()
    {

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', 'https://www.tuttur.com/draw/events/type/football');
            $response = json_decode($response->getBody());

            $r = $response->events;

            $result = array("message" => "");

            foreach ($r as $key => $val) {
                if ($key >= 100) {
                    $event = array(
                        "event_oid" => $r->$key->code,
                        "type" => $r->$key->type,
                        "start_date" => $r->$key->startDate,
                        "country" => $r->$key->country,
                        "league_name" => $r->$key->leagueName,
                        "league_code" => $r->$key->leagueCode,
                        "mbc" => $r->$key->mbc,
                        "home" => $r->$key->homeTeamName,
                        "away" => $r->$key->awayTeamName,
                        "identifier_id" => $r->$key->identifier
                    );

                    $check = Events::where('identifier_id', $r->$key->identifier)->count();
                    if ($check == 0) {
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
                                            "odd_option" => $option,
                                            "odd" => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id" => $event_id
                                        );


                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status" => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " " . $option . " Seçeneği Eklendi"
                                            );
                                        }

                                    } else {
                                        $result = array(
                                            "status" => FALSE,
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
                                                "odd_option" => "Var",
                                                "odd" => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id" => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status" => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " VAR Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                        if ($k == "SC.NG") {
                                            // YOK
                                            $odd_option = array(
                                                "odd_option" => "Yok",
                                                "odd" => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id" => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status" => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " YOK Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                    } else {
                                        $result = array(
                                            "status" => FALSE,
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
                                                "odd_option" => "Çift",
                                                "odd" => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id" => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status" => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Çift Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                        if ($k == "F.O") {
                                            // TEK
                                            $odd_option = array(
                                                "odd_option" => "Tek",
                                                "odd" => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id" => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status" => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Tek Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                    } else {
                                        $result = array(
                                            "status" => FALSE,
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
                                                "odd_option" => "Altı",
                                                "odd" => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id" => $event_id
                                            );
                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status" => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                        if ($k == "OVER") {
                                            // YOK
                                            $odd_option = array(
                                                "odd_option" => "Üstü",
                                                "odd" => $v,
                                                "odd_type_id" => $oddType->odd_type_id,
                                                "event_id" => $event_id
                                            );

                                            if (DB::table('odd_options')->insert($odd_option)) {
                                                $result = array(
                                                    "status" => FALSE,
                                                    "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Üstü Seçeneği Eklendi"
                                                );
                                            }
                                        }
                                    } else {
                                        $result = array(
                                            "status" => FALSE,
                                            "message" => "OVER Sorgusu Çalışmadı!"
                                        );
                                    }

                                }

                            }
                        } else {
                            $result = array(
                                "status" => FALSE,
                                "message" => $event["home"] . " - " . $event["away"] . " Maçı sistemde bulunmaktadır"
                            );
                        }
                    }

                } else {
                    $result = array(
                        "status" => FALSE,
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
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://www.tuttur.com/draw/events/type/basketball');
        $response = json_decode($response->getBody());

        $r = $response->events;

        $result = array("message" => "");
        foreach ($r as $key => $val) {
            if ($key >= 100) {
                $lmt = "*LIMIT*";
                $event = array(
                    "event_oid" => $r->$key->code,
                    "type" => $r->$key->type,
                    "start_date" => $r->$key->startDate,
                    "country" => $r->$key->country,
                    "league_name" => $r->$key->leagueName,
                    "league_code" => $r->$key->leagueCode,
                    "mbc" => $r->$key->mbc,
                    "home" => $r->$key->homeTeamName,
                    "away" => $r->$key->awayTeamName,
                    "identifier_id" => $r->$key->identifier,
                    "s1Handicap" => $r->$key->s1Handicap,
                    "f1Handicap" => $r->$key->extraHomeHandicap,
                    "totalLimit" => $r->$key->odds->$lmt
                );

                $check = Events::where('identifier_id', $r->$key->identifier)->count();
                if ($check == 0) {
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
                                        "odd_option" => $option,
                                        "odd" => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id" => $event_id
                                    );
                                    if (DB::table('odd_options')->insert_odds($odd_option)) {
                                        $result = array(
                                            "status" => FALSE,
                                            "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " " . $option . " Seçeneği Eklendi"
                                        );
                                    }
                                } else {
                                    $result = array(
                                        "status" => FALSE,
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
                                            "odd_option" => "Var",
                                            "odd" => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id" => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status" => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " VAR Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                    if ($k == "SC.NG") {
                                        // YOK
                                        $odd_option = array(
                                            "odd_option" => "Yok",
                                            "odd" => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id" => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status" => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " YOK Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                } else {
                                    $result = array(
                                        "status" => FALSE,
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
                                            "odd_option" => "Çift",
                                            "odd" => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id" => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status" => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Çift Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                    if ($k == "F.O") {
                                        // TEK
                                        $odd_option = array(
                                            "odd_option" => "Tek",
                                            "odd" => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id" => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status" => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Tek Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                } else {
                                    $result = array(
                                        "status" => FALSE,
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
                                        "odd_option" => "1 (" . $r->$key->extraHomeHandicap . ")",
                                        "odd" => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id" => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status" => FALSE,
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
                                        "odd_option" => "2 (" . ($r->$key->extraHomeHandicap * -1) . ")",
                                        "odd" => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id" => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status" => FALSE,
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
                                        "odd_option" => "1 (" . ($r->$key->s1Handicap) . ")",
                                        "odd" => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id" => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status" => FALSE,
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
                                        "odd_option" => "2 (" . ($r->$key->s1Handicap * -1) . ")",
                                        "odd" => $v,
                                        "odd_type_id" => $oddType->odd_type_id,
                                        "event_id" => $event_id
                                    );
                                    if (DB::table('odd_options')->insert($odd_option)) {
                                        $result = array(
                                            "status" => FALSE,
                                            "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                        );
                                    }
                                }
                            }
                            if ($k == "UNDER" || $k == "OVER") {
                                $oddType = DB::table('odd_types')->where('odd_type_code', 'OVER');
                                if ($oddType) {
                                    $oddType = $oddType->first();
                                    if ($k == "UNDER") {
                                        // VAR
                                        $odd_option = array(
                                            "odd_option" => $r->$key->odds->$lmt . " Altı",
                                            "odd" => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id" => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status" => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Altı Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                    if ($k == "OVER") {
                                        // YOK
                                        $odd_option = array(
                                            "odd_option" => $r->$key->odds->$lmt . " Üstü",
                                            "odd" => $v,
                                            "odd_type_id" => $oddType->odd_type_id,
                                            "event_id" => $event_id
                                        );
                                        if (DB::table('odd_options')->insert($odd_option)) {
                                            $result = array(
                                                "status" => FALSE,
                                                "message" => $result["message"] . "\n" . $event_id . " ID li maça " . $oddType->odd_type . " Üstü Seçeneği Eklendi"
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $result = array(
                            "status" => FALSE,
                            "message" => $event["home"] . " - " . $event["away"] . " Maçı sistemde bulunmaktadır"
                        );
                    }
                }
            } else {
                $result = array(
                    "status" => FALSE,
                    "message" => $result["message"] . "\n" . " Maç Uzun Vadeli"
                );
            }
        }
        echo json_encode($result);
    }

    public function events(Request $request)
    {

        try {
            $now = new DateTime();
            $today = $now->getTimestamp();
            $tomorrow = $now->modify('+1 Day')->getTimestamp();

            $query = TReq::multiple($request, Events::class);
            $data = $query['query']->where('start_date', '>=', $today)->where('start_date', '<=', $tomorrow)->get();

            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ], 'data' => $data
            ];
            foreach ($data as $k => $v) {
                $time = new DateTime();
                $time->setTimestamp($data[$k]->start_date);
                $data[$k]->start_date = $time->format("H:i");
            }
            return Res::success(200, 'Maçlar', $result);

        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function odds($event_id)
    {
        try {
            $now = new DateTime();

            $events = Events::where('event_id', $event_id)->where('start_date', '>=', $now->getTimestamp());

            if ($events->count() > 0) {

                $odd_group = DB::table('odd_options')->select('odd_types.odd_type', 'odd_options.odd_type_id')
                    ->join('odd_types', function ($join) {
                        $join->on('odd_types.odd_type_id', '=', 'odd_options.odd_type_id');
                    })->where('event_id', '=', $event_id)
                    ->orderBy('odd_type_id', 'ASC')
                    ->groupBy('odd_type', 'odd_type_id')
                    ->get();

                foreach ($odd_group as $k => $v) {
                    $odd_group[$k]->options = DB::table('odd_options')->select('odd_option', 'odd', 'odd_option_id')
                        ->join('odd_types', 'odd_types.odd_type_id', 'odd_options.odd_type_id', 'left')
                        ->where('event_id', $event_id)
                        ->where('odd_options.odd_type_id', $odd_group[$k]->odd_type_id)->get();
                }

                return Res::success(200, 'Maçlar', $odd_group);

            } else {
                throw new Exception("Couldn't find the event", Response::HTTP_NOT_FOUND);
            }
        } catch (exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function send(Request $request)
    {
        try{

            $odd    = 1;
            $ready  = true;
            $events = [];

            $validator = Validator::make($request->all(), [
                'odd'           => 'required',
                'home'          => 'required',
                'away'          => 'required',
                'fold'          => 'required',
                'odd_type'      => 'required',
                'odd_option'    => 'required',
                'start_date'    => 'required',
                'description'   => 'required',
                'odd_option_id' => 'required',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
            }

            $event = [
                "evsahibi"      => $request->home,
                "deplasman"     => $request->away,
                "mac_tarihi"    => $request->start_date,
                "bet"           => $request->odd_type."(" . $request->odd_option.")",
                "oran"          => $request->odd,
                "durum"         => 0,
                "odd_option_id" => $request->odd_option_id
            ];

            if(time() > $request->start_date){
                throw new Exception('Seçilen Karşılaşma Aktif Değil!', Response::HTTP_INTERNAL_SERVER_ERROR);
            }else{
                $odd *= $event['oran'];

                array_push($events, $event);
            }

            if($request->fold <= 250 && $request->fold >= 3){

                $coupon = Coupon::create([
                        'kupon_sahibi' => $request->user()->ID,
                        'kupon_sonucu' => 'DEVAMEDIYOR',
                        'misli'        => $request->fold,
                        'aciklama'     => $request->description,
                        'kazanc'       => $request->fold * $odd,
                ]);

                foreach($events as $k => $v){

                    $now = new DateTime();
                    $now->setTimestamp($events[$k]['mac_tarihi']);

                    $events[$k]['kupon_id']   = $coupon->kupon_id;
                    $events[$k]['mac_tarihi'] = $now->format("Y-m-d H:i:s");

                    if(DB::table('tb_maclar')->insert($events[$k])){
                        $return = true;
                    }else {
                        $return = false;
                    }
                }

                if($return == true){

                    if(Posts::create([
                        'durum' => $coupon->kupon_id,
                        'kullanici_id' => $coupon->kupon_sahibi,
                        'paylasim_tipi' => 2
                    ])){
                        return "old";
                    }

                }



            }




            return res::success(200, 'comment', $events);

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

}

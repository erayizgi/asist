<?php

namespace App\Http\Controllers;


use App\Follow;
use App\Notifications;
use App\Points;
use App\Posts;
use Carbon\Carbon;
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
		error_reporting(E_ALL ^ E_NOTICE);
		$client = new \GuzzleHttp\Client();

		$response = $client->request('GET', 'https://www.tuttur.com/draw/events/type/basketball');
		$response = json_decode($response->getBody());

		$r = $response->events;

		$result = array("message" => "");
		foreach ($r as $key => $val) {
			if ($key >= 100) {
				$lmt = "LIMIT";
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
					"totalLimit" => $r->$key->odds->{'*'.$lmt.'*'}
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

			$query = TReq::multiple($request, Events::class);
			$data = $query['query']->where('start_date', '>=', $today)->orderBy("start_date", "ASC")->get();

			$dates = DB::table("events")->distinct()->select(DB::raw("FROM_UNIXTIME(`start_date`,'%d-%m-%Y') AS 'date_formatted'"))
				->where("start_date", ">=", $today)
				->orderBy("start_date", "ASC")
//				->groupBy("date_formatted")
				->pluck("date_formatted")
				->all();
			$leagues = DB::table("events")->distinct()->select("league_name","league_code")
				->where("start_date", ">=", $today)
				->orderBy("league_name", "ASC")
				->get();
			$result = [
				'metadata' => [
					'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $data,
				"dates" => $dates,
				"leagues" => $leagues
			];
			foreach ($data as $k => $v) {
				$time = new DateTime();
				$time->setTimestamp($data[$k]->start_date);
				$data[$k]->start_day = $time->format("Y-m-d");
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
		try {
			$coupon = $request->coupon;
			$events = $request->events;
			$mbs = 0;
			$totalOdd = 1;
			$couponEvents = [];
			foreach ($events as $k => $v) {
				$event = Events::find($events[$k]["event_id"]);
				if (!$event) {
					throw new Exception("Seçtiğiniz maç bulunamadı", Response::HTTP_NOT_FOUND);
				}
				if ($event->start_date < time()) {
					throw new Exception("Kuponunuzdaki maçlardan biri başlamış bulunmaktadır", Response::HTTP_BAD_REQUEST);
				}
				if ($event->mbc > $mbs) {
					$mbs = $event->mbc;
				}
				$totalOdd = $totalOdd * $events[$k]["odd"];
				$couponEvents[] = [
					"evsahibi" => $event->home,
					"deplasman" => $event->away,
					"mac_tarihi" => Carbon::createFromTimestamp($event->start_date)->format("Y-m-d H:i:s"),
					"bet" => $events[$k]["odd_type"],
					"oran" => $events[$k]["odd"],
					"durum" => 0,
					"odd_option_id" => $events[$k]["odd_option_id"],
					"kupon_id" => 0
				];
			}
			if (count($events) < $mbs) {
				throw new Exception("Kuponunuz için Minimum Bahis Sayısı " . $mbs . " Lütfen kuponunuzu güncelleyin", Response::HTTP_BAD_REQUEST);
			}
			$aciklama = (isset($coupon["aciklama"])) ? $coupon["aciklama"] : "";
			$kazanc = $coupon["misli"] * $totalOdd;
			$createdCoupon = Coupon::create([
				"kupon_sahibi" => $request->user()->ID,
				"kupon_sonucu" => "DEVAMEDIYOR",
				"misli" => $coupon["misli"],
				"aciklama" => $aciklama,
				"paylasilma_tarihi" => Carbon::now()->format("Y-m-d H:i:s"),
				"kazanc" => $kazanc
			]);
			if ($createdCoupon) {
				Points::create([
					'user_id' => $request->user()->ID,
					'amount' => $coupon["misli"] * -1,
					'operation_id' => $createdCoupon->kupon_id,
					'operation_type' => "KUPON",
				]);
				foreach ($couponEvents as $k => $v) {
					$couponEvents[$k]["kupon_id"] = $createdCoupon->kupon_id;
				}
				DB::table('tb_maclar')->insert($couponEvents);
				$post = Posts::create([
					'durum' => $createdCoupon->kupon_id,
					'kullanici_id' => $createdCoupon->kupon_sahibi,
					'paylasim_tipi' => 2
				]);
				$followers = Follow::select("takipEdenID")->where("takipEdilenID", $createdCoupon->kupon_sahibi)->get();
				$noti = [];
				foreach ($followers as $f) {
					$noti[] = [
						"alici_id" => $f->takipEdenID,
						"bildirim_tipi" => 'kupon',
						"bildirim_url" => $request->user()->kullaniciAdi . "/posts/" . $post->paylasim_id,
						"olusturan_id" => $request->user()->ID
					];
				}

				if (!Notifications::insert($noti)) {
					throw new Exception('notification errors', Response::HTTP_INTERNAL_SERVER_ERROR);
				}
			}
			return res::success(Response::HTTP_CREATED, 'Kupon başarıyla kaydedildi');

		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

}

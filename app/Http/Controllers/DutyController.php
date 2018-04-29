<?php

namespace App\Http\Controllers;

use App\Comments;
use App\Coupon;
use App\Duty;
use App\DutyGroup;
use App\Follow;
use App\Forecast;
use App\Libraries\Res;
use App\Libraries\TReq;
use App\Points;
use App\Posts;
use App\UserDuties;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DutyController extends Controller
{
	public function check()
	{
		/*
		 * ___gorev_islem_tipleri___
		 * 1	post
		 * 2	rePost
		 * 3	comment
		 * 4	follower
		 * 5	following
		 * 6	coupon
		 */
		/* GÖREV İŞLEM TÜRLERİ
		 * 1	post
		 * 2	comment
		 * 3	forecast_comment
		 * 4	coupon
		 * 5	forecast
		 */
		try {
			$duties = UserDuties::with(["duty", "duty.dutyGroup"])->get();
//			return $duties;
			foreach ($duties as $k => $v) {
				switch ($v->duty->islem_tipi) {
					case 2: // işlem tipi rePost ise
						if ($v->duty->icerik_turu == 4) { // içerik türü kupon ise
							$rePosts = Posts::where("tb_paylasimlar.created_at", ">=", $v->created_at)
								->where("tb_paylasimlar.paylasim_tipi", 3)
								->where("tb_paylasimlar.kullanici_id", $v->kullanici_id)
								->join(DB::raw("tb_paylasimlar a"), "a.paylasim_id", "tb_paylasimlar.paylasilan_gonderi")
								->where(DB::raw("a.paylasim_tipi"), 2)
								->count();
							if ($rePosts >= $v->duty->gorev_hedefi) {
								UserDuties::where("kg_id", $v->kg_id)->update(["tamamlandi" => 1]);
								Points::create([
									'user_id' => $v->kullanici_id,
									'amount' => $v->duty->odul,
									'operation_id' => $v->kg_id,
									'operation_type' => "GOREV_TAMAMLANDI",
								]);
							}
						}
						break;
					case 3: // işlem tipi comment ise
						if ($v->duty->icerik_turu == 3) { // içerik türü forecast_comment
							$forecast = Forecast::select(DB::raw("MAX(created_at)"))->where('tahminci_id', $v->kullanici_id)
								->where("created_at", ">=", $v->created_at)
								->groupBy("mac_id")
								->count();
							if ($forecast >= $v->duty->gorev_hedefi) {
								UserDuties::where("kg_id", $v->kg_id)->update(["tamamlandi" => 1]);
								Points::create([
									'user_id' => $v->kullanici_id,
									'amount' => $v->duty->odul,
									'operation_id' => $v->kg_id,
									'operation_type' => "GOREV_TAMAMLANDI",
								]);
							}
						} elseif ($v->duty->icerik_turu == 4) {
							$commentsToCoupons = Comments::select(DB::raw("MAX(tb_paylasimlar.paylasim_id)"))
								->where("tb_paylasim_yorumlari.created_at", ">=", $v->created_at)
								->where("tb_paylasim_yorumlari.kullanici_id", $v->kullanici_id)
								->join("tb_paylasimlar", "tb_paylasimlar.paylasim_id", "tb_paylasim_yorumlari.paylasim_id")
								->where("tb_paylasimlar.paylasim_tipi", 2)
								->where("tb_paylasimlar.kullanici_id", "!=", $v->kullanici_id)
								->groupBy("tb_paylasimlar.paylasim_id")
								->count();
							if ($commentsToCoupons >= $v->duty->gorev_hedefi) {
								UserDuties::where("kg_id", $v->kg_id)->update(["tamamlandi" => 1]);
								Points::create([
									'user_id' => $v->kullanici_id,
									'amount' => $v->duty->odul,
									'operation_id' => $v->kg_id,
									'operation_type' => "GOREV_TAMAMLANDI",
								]);
							}
						}
						break;
					case 4:
						$followers = Follow::where("takipEdilenID", $v->kullanici_id)
							->where("olusturulmaTarihi", ">=", $v->created_at)
							->where("created_at", ">=", $v->created_at)
							->count();
						if ($followers >= $v->duty->gorev_hedefi) {
							UserDuties::where("kg_id", $v->kg_id)->update(["tamamlandi" => 1]);
							Points::create([
								'user_id' => $v->kullanici_id,
								'amount' => $v->duty->odul,
								'operation_id' => $v->kg_id,
								'operation_type' => "GOREV_TAMAMLANDI",
							]);
						}
						break;
					case 5:
						$followers = Follow::where("takipEdenID", $v->kullanici_id)
							->where("olusturulmaTarihi", ">=", $v->created_at)
							->where("created_at", ">=", $v->created_at)
							->count();
						if ($followers >= $v->duty->gorev_hedefi) {
							UserDuties::where("kg_id", $v->kg_id)->update(["tamamlandi" => 1]);
							Points::create([
								'user_id' => $v->kullanici_id,
								'amount' => $v->duty->odul,
								'operation_id' => $v->kg_id,
								'operation_type' => "GOREV_TAMAMLANDI",
							]);
						}
						break;
					case 6:
						$coupons = Coupon::where("paylasilma_tarihi", ">=", $v->created_at)
							->where("created_at", ">=", $v->created_at);
						if ($v->duty->icerik_turu === null) {
							//TODO: lig kontrolü burda yapılabilir
						}
						$coupons->where("kupon_sonucu", $v->duty->icerik_durumu)
							->where("kupon_sahibi", $v->kullanici_id);
						$cp = $coupons->get();
						$coupons = $coupons->count();
						return $coupons;
						if ($coupons >= $v->duty->gorev_hedefi) {
							UserDuties::where("kg_id", $v->kg_id)->update(["tamamlandi" => 1]);
							if ($v->duty->odul_islem === "+") {
								Points::create([
									'user_id' => $v->kullanici_id,
									'amount' => $v->duty->odul,
									'operation_id' => $v->kg_id,
									'operation_type' => "GOREV_TAMAMLANDI",
								]);
							} else {
								foreach ($cp as $c) {
									Points::create([
										'user_id' => $v->kullanici_id,
										'amount' => $v->duty->odul * $c->kesinKazanc,
										'operation_id' => $v->kg_id,
										'operation_type' => "GOREV_TAMAMLANDI",
									]);
								}
							}

						}

//						$coupons = Coupon::where()
						break;
				}
			}
		} catch (\Exception $e) {
			return $e;
		}
//		return $duties;
	}

	public function dutyGroups(Request $request)
	{
		try {
			$query = Treq::multiple($request, DutyGroup::class);
			$data = $query["query"]->with("duties")->orderBy("created_at", "DESC");
			$result = [
				'metadata' => [
					'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $data->get(),
			];
			return Res::success(200, 'Duty Groups', $result);
		} catch (\Exception $e) {
			return Res::fail($e->getcode(), $e->getMessage());
		}
	}

	public function duties(Request $request, $group_id)
	{
		try {
			$group = DutyGroup::where("grup_id", $group_id)->first();
			if (!$group) {
				throw new \Exception("Verilen görev grubu bulunamadı", 404);
			}
			$query = TReq::multiple($request, Duty::class);
			$data = $query["query"]->where("grup_id", $group_id);
			$result = [
				'metadata' => [
					'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $data->get(),
			];
			return Res::success(200, 'Duties of Duty Group', $result);
		} catch (\Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function assignDuty(Request $request, $duty_group_id)
	{
		try {
			$duty = DutyGroup::where("grup_id",$duty_group_id)->first();
			if (!$duty) {
				throw new \Exception("Katılmak istediğiniz yarışma bulunamadı", Response::HTTP_NOT_FOUND);
			}
			// kullanıcnın içinde bulunduğu bir görev grubu var mı
			$activeDuty = UserDuties::where("tamamlandi", 0)->where("kullanici_id",$request->user()->ID)->count();
			if ($activeDuty > 0) {
				throw new \Exception("Aktif katılımınızın olduğu bir görev grubu bulunmaktadır.", Response::HTTP_FORBIDDEN);
			}
			if ($duty->onkosullu_grup != null) {
				$checkForPreReq = UserDuties::where("grup_id", $duty->onkosullu_grup->grup_id)
					->where("tamamlandi", 0)
					->where("kullanici_id",$request->user()->ID)
					->count();
				if ($checkForPreReq > 0){
					throw new \Exception("Bu görev grubuna katılabilmeniz için ön koşulu tamamlamanız gerekmektedir",Response::HTTP_FORBIDDEN);
				}
			}
			$duties = Duty::where("grup_id",$duty_group_id)->get();
			$userDuty = [];
			foreach($duties as $d){
				$userDuty[] = [
					"grup_id" => $d->grup_id,
					"gorev_id" => $d->gorev_id,
					"kullanici_id" => $request->user()->ID,
					"tamamlandi" => 0
				];
			}
			UserDuties::insert($userDuty);
			return Res::success(Response::HTTP_CREATED,"Göreve Başarıyla Katıldınız");
		} catch (\Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function getActiveDutyGroup(Request $request)
	{
		try{
			$find = UserDuties::select("grup_id")->where("kullanici_id",$request->user()->ID)->where("tamamlandi",0)->first();
			return Res::success(Response::HTTP_OK,"Aktif Görev",$find);
		}catch (\Exception $e){
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}
}

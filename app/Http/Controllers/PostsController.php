<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Comments;
use App\Likes;
use App\User;
use Carbon\Carbon;
use DB;
use App\Coupon;
use App\Events;
use App\Follow;
use App\Games;
use App\Posts;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Notifications;
use Illuminate\Support\Facades\Validator;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Validation\ValidationException;

class PostsController extends Controller
{
    //
    public function posts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'resim' => 'sometimes|required|filled',
                'paylasim_tipi' => 'required|filled',
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $post = Posts::create([
                'paylasim_tipi' => $request->paylasim_tipi,
                'durum' => $request->durum,
                'kullanici_id' => $request->user()->ID,
                'resim' => $request->resim,
                'paylasilan_gonderi' => $request->paylasilan_gonderi
            ]);
            if(!$post){
                throw new Exception("Post oluşturulamadı",Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $followers = DB::table("tb_takip")->where("takipEdilenID", $request->user()->ID)->where("kayitDurumu", 1)->get();
            $bildirimler = [];
            if($request->paylasim_tipi === 1){
                $tip = "post";
            }elseif($request->paylasim_tipi === 2){
                $tip = "kupon";
            }else{
                $tip = "postOnWall";
            }

            foreach ($followers as $f) {
                array_push($bildirimler, [
                    "alici_id" => $f->takipEdenID,
                    "bildirim_tipi" => $tip,
                    "bildirim_url" => $request->user()->kullaniciAdi.'/posts/'.$post->paylasim_id,
                    "olusturan_id" => $request->user()->ID
                ]);
            }
            Activities::create([
                "kullanici_id" => $request->user()->ID,
                "islem_turu" => $tip,
                "islem_id" => $post->paylasim_id,
                "islem_tarihi" => Carbon::now()->format("Y-m-d H:i:s")
            ]);
            if (!Notifications::insert($bildirimler)) {
                throw new Exception('Bildirim oluşturulamadı', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return Res::success(200, 'Durum paylaşıldı', $post);
        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function myFeed(Request $request)
    {
        try {
            $query = TReq::multiple($request, Follow::class);
            $user_id = $request->user()->ID;
            /*
             * select * from tb_takip
             * INNER JOIN tb_paylasimlar ON tb_paylasimlar.kullanici_id = tb_takip.takipEdilenID
             * WHERE tb_takip.takipEdenID = 25
             */
            $data = $query['query']->join("tb_paylasimlar", "tb_paylasimlar.kullanici_id", "tb_takip.takipEdilenID")
                ->select(
                    'tb_paylasimlar.created_at AS post_created_at',
                    'tb_paylasimlar.paylasim_id AS post_id',
                    'tb_paylasimlar.*',
                    'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id")
                ->where("tb_takip.takipEdenID", "=", $user_id);
            //return $data->toSql();
            $data = $data->get();
            foreach ($data as $k => $v) {
                $data[$k]->post_id = encrypt($v->post_id);
            }
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];

            return Res::success(200, 'Feed', $result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }

    }

    public function likeCount($post)
    {
        try {
            $data = Likes::where(["paylasim_id" => $post])->count();
            return Res::success(200, 'likeCount', $data);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function post($post_id)
    {
        try {
            $data = Posts::where("paylasim_id", $post_id)
                ->select("tb_paylasimlar.*", 'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id", "inner")
                ->first();
            return Res::success(200, 'Post', $data);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function getByComment($comment_id)
    {
        try {
            $data = Comments::where("yorum_id", $comment_id)->
            select(
                "tb_paylasim_yorumlari.yorum",
                "tb_paylasim_yorumlari.created_at as yorum_tarihi",
                "tb_kullanicilar.IMG",
                "tb_kullanicilar.adSoyad",
                "tb_kullanicilar.kullaniciAdi",
                "tb_paylasimlar.paylasim_id",
                "tb_paylasimlar.durum",
                "tb_paylasimlar.resim",
                "tb_paylasimlar.paylasim_tipi",
                "tb_paylasimlar.paylasilan_gonderi",
                "tb_paylasimlar.created_at"
            )
                ->join("tb_paylasimlar", "tb_paylasimlar.paylasim_id", "tb_paylasim_yorumlari.paylasim_id")
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id")->first();
            return Res::success(200, 'Post', $data);

        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function couponGames(Request $request, $coupon_id)
    {
        try {
            $games = Games::where("kupon_id", $coupon_id)->get();
            $result = [
                'metadata' => [
                    'count' => $games->count()
                ],
                'data' => $games
            ];
            return Res::success(200, 'Games', $result);

        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function couponStatus($coupon_id)
    {
        try {
            $games = Coupon::where("kupon_id", $coupon_id)->first();
            $result = [
                'metadata' => [
                    'count' => 1
                ],
                'data' => $games
            ];
            return Res::success(200, 'Coupon', $result);

        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

	public function getFeedOfUser(Request $request, $username)
	{
		try {
			$user = User::where("kullaniciAdi", $username)->first();
			$query = TReq::multiple($request, Posts::class);
			$data = $query["query"]
				->select(
					'tb_paylasimlar.created_at AS post_created_at',
					'tb_paylasimlar.paylasim_id AS post_id',
					'tb_paylasimlar.*',
					'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
				->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id")
				->where("tb_paylasimlar.kullanici_id", $user->ID)
				->orderBy("post_created_at", "DESC");
			$result = [
				'metadata' => [
					'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $data->get()
			];

            return Res::success(200, 'Feed', $result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function like(Request $request)
    {
        try{
            $check = Likes::where(["begenen_id"=>$request->user()->ID,"paylasim_id" => $request->post_id])->count();
            if($check > 0){
                $data = Likes::where(["begenen_id"=>$request->user()->ID,"paylasim_id"=>$request->post_id])->delete();
                $result = false;
            }else{
                $data = Likes::create([
                    "begenen_id"=> $request->user()->ID,
                    "paylasim_id" => $request->post_id,
                    "begenilen_tarih" => date("Y-m-d H:i:s")
                ]);
                $result = true;
            }

            return Res::success(201,"Liked",$result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function hasLiked(Request $request, $post_id)
    {
        try{
            $check = Likes::where(["begenen_id"=>$request->user()->ID,"paylasim_id" => $post_id])->count();
            return Res::success(200,"HasLiked",($check > 0)? true : false);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function likers($post_id)
    {
        try{
            $check = Likes::where(["paylasim_id" => $post_id])
                ->select('tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_kullanicilar","tb_kullanicilar.ID","tb_begeni.begenen_id")
                ->get();
            return Res::success(200,"Likers",$check);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

	public function delete(Request $request, $post_id)
	{

		try {

			$post_type = Posts::where([
				'paylasim_id' => $request->post_id,
				'kullanici_id' => $request->user()->ID
			])->first();

			if (!$post_type) {
				throw new Exception('Böyle Bir Paylaşım Bulunamadı!', Response::HTTP_NOT_FOUND);
			}

			if ($post_type->paylasim_tipi == 2) {

				// Maçlar
				$check = Games::where('kupon_id', $post_type->durum)->where('mac_tarihi', '<=', Carbon::now()->format('Y-m-d H:i:s'))->count();

				if ($check > 0) {
					throw new Exception('Yapmış Olduğunuz Kuponda Başlayan Maç Olduğundan Paylaşımı Silemezsiniz!', Response::HTTP_FORBIDDEN);
				} else {
					// Delete from games
					$game = Games::where('kupon_id', $post_type->durum)->delete();
				}
			}

			// Delete from post comments
			$comment = Comments::where('paylasim_id', $request->post_id)->delete();

			// Detele from likes
			$like = Likes::where('paylasim_id', $request->post_id)->delete();

			// Delete from activity
			$activity = Activities::where('islem_id', $request->post_id)->delete();

			$post_type->delete();

			return Res::success(200, "Seçmiş Olduğunuz Paylaşım Başarılı Bir Şekilde Silindi!", Response::HTTP_OK);

		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}
}

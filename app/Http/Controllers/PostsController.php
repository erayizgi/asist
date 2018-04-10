<?php

namespace App\Http\Controllers;

use App\Comments;
use DB;
use App\Coupon;
use App\Follow;
use App\Games;
use App\Posts;
use Exception;
use Illuminate\Http\Request;
use App\Notifications;
use Illuminate\Support\Facades\Validator;
use App\Libraries\TReq;
use App\Libraries\Res;

class PostsController extends Controller
{
    //
    public function posts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'paylasim_tipi' => 'required|filled',
                'durum' => 'required|filled',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $post = Posts::create([
                'paylasim_tipi' => $request->paylasim_tipi,
                'durum' => $request->durum,
                'kullanici_id' => $request->user()->ID,
                'resim' => $request->resim,
                'paylasilan_gonderi' => $request->paylasilan_gonderi
            ]);


            $followers = DB::table("tb_takip")->where("takipEdilenID", $request->user()->ID)->where("kayitDurumu", 1)->get();
            $bildirimler = [];
            $tip = "durum";

            foreach ($followers as $f) {
                array_push($bildirimler, [
                    "alici_id" => $f->takipEdenID,
                    "bildirim_tipi" => $tip,
                    "bildirim_url" => "notify_url",
                    "olusturan_id" => $request->user()->ID
                ]);
            }

            if (!Notifications::insert($bildirimler)) {
                throw new Exception('notification errors', 400);
            }

            return Res::success(200, 'Durum paylaşıldı', $post);

        } catch (Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => [
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500, $message, $error);
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
            $error = new \stdClass();
            $error->errors = [
                'exception' => [
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $e->getMessage(), $error);
        }

    }

    public function post($post_id)
    {
        try {
            $data = Posts::where("paylasim_id", $post_id)
                ->select("tb_paylasimlar.*",'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id", "inner")
                ->first();
            return Res::success(200, 'Post', $data);
        } catch (Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => [
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500, $e->getMessage(), $error);
        }
    }

    public function getByComment($comment_id)
    {
        try{
            $data = Comments::where("yorum_id",$comment_id)->
                select(
                    "tb_paylasim_yorumlari.yorum",
                    "tb_paylasim_yorumlari.created_at as yorum_tarihi",
                    "tb_kullanicilar.IMG",
                    "tb_kullanicilar.adSoyad",
                    "tb_kullanicilar.kullaniciAdi",
                    "tb_paylasimlar.durum",
                    "tb_paylasimlar.resim",
                    "tb_paylasimlar.paylasim_tipi",
                    "tb_paylasimlar.paylasilan_gonderi"
                )
                ->join("tb_paylasimlar","tb_paylasimlar.paylasim_id","tb_paylasim_yorumlari.paylasim_id")
                ->join("tb_kullanicilar","tb_kullanicilar.ID","tb_paylasimlar.kullanici_id")->first();
            return Res::success(200, 'Post', $data);

        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception' => [
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500, $e->getMessage(), $error);
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
            $error = new \stdClass();
            $error->errors = [
                'exception' => [
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $e->getMessage(), $error);
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
            $error = new \stdClass();
            $error->errors = [
                'exception' => [
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $e->getMessage(), $error);
        }
    }
}

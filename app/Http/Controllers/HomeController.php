<?php

namespace App\Http\Controllers;

use App\Comments;
use App\Sliders;
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

class HomeController extends Controller
{


    public function posts(Request $request){
        try {
            $query = TReq::multiple($request, Posts::class);
            $data = $query['query']->select(
                'tb_paylasimlar.created_at AS post_created_at',
                'tb_paylasimlar.paylasim_id AS post_id',
                'tb_paylasimlar.*',
                'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id")
                ->where([
                    'kayitDurumu' => 1,
                    'paylasim_tipi' => 1])
                ->orderBy('paylasim_id', 'DESC')
                ->get();

            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];
            return Res::success(200, 'Posts', $result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function coupons(Request $request){
        try {
            $query = TReq::multiple($request, Posts::class);
            $data  = $query['query']->select('tb_kuponlar.*', 'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                                    ->join("tb_kuponlar", "tb_kuponlar.kupon_id", "tb_paylasimlar.durum")
                                    ->join('tb_kullanicilar', 'tb_kullanicilar.ID', 'tb_kuponlar.kupon_sahibi')
                                    ->where([
                                        'kayitDurumu'   => 1,
                                        'paylasim_tipi' => 1
                                    ])
                                    ->orderBy("kesinKazanc", "DESC")
                                    ->get();
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];
            return Res::success(200, 'Posts', $result);

        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function population(){
        try{
            $data  = DB::select("SELECT tb_kullanicilar.adSoyad, tb_kullanicilar.IMG, tb_kullanicilar.kullaniciAdi, ((SELECT COUNT(tb_kuponlar.kupon_id) FROM tb_kuponlar WHERE tb_kuponlar.kupon_sahibi = tb_kullanicilar.ID AND tb_kuponlar.kupon_sonucu = 'KAZANDI')/(SELECT COUNT(tb_kuponlar.kupon_id) FROM tb_kuponlar WHERE tb_kuponlar.kupon_sahibi = tb_kullanicilar.ID AND (tb_kuponlar.kupon_sonucu = 'KAZANDI' OR tb_kuponlar.kupon_sonucu='KAYBETTI')))*100 AS yuzde FROM `tb_kullanicilar` WHERE `populer` = 1 ORDER BY `yuzde` DESC");
            return Res::success(200, 'navigation tab menu sliders', $data);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }




}

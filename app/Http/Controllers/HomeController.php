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


    public function posts(Request $request)
    {
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

    public function coupons(Request $request)
    {
        try {
            $query = TReq::multiple($request, Posts::class);
            $data = $query['query']
                ->select('tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi','tb_paylasimlar.*',
                    'tb_kuponlar.kesinKazanc','tb_kuponlar.kazanc')
                ->join("tb_kuponlar", "tb_kuponlar.kupon_id", "tb_paylasimlar.durum")
                ->join('tb_kullanicilar', 'tb_kullanicilar.ID', 'tb_kuponlar.kupon_sahibi')
                ->where([
                    'kayitDurumu' => 1,
                    'paylasim_tipi' => 2
                ])
//                "SQLSTATE[42000]: Syntax error or access violation: 1066 Not unique table/alias: 'tb_paylasimlar' (SQL:
//                select `tb_kuponlar`.*, `tb_kullanicilar`.`adSoyad`, `tb_kullanicilar`.`IMG`, `tb_kullanicilar`.`kullaniciAdi` from `tb_paylasimlar`
//                inner join `tb_kuponlar` on `tb_kuponlar`.`kupon_id` = `tb_paylasimlar`.`durum`
//                inner join `tb_kullanicilar` on `tb_kullanicilar`.`ID` = `tb_kuponlar`.`kupon_sahibi`
//                inner join `tb_paylasimlar` on `tb_paylasimlar`.`durum` = `tb_kuponlar`.`kupon_id`
//                where (`kayitDurumu` = 1 and `paylasim_tipi` = 1) order by `kesinKazanc` desc limit 20 offset 0)"

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

            /*
             * SELECT tb_kuponlar.*,
  tb_paylasimlar.durum,tb_kullanicilar.adSoyad, tb_kullanicilar.kullaniciAdi,
  tb_kullanicilar.IMG
FROM tb_kuponlar
  INNER JOIN tb_paylasimlar ON tb_paylasimlar.durum = tb_kuponlar.kupon_id
  INNER JOIN tb_kullanicilar ON tb_kullanicilar.ID = tb_kuponlar.kupon_sahibi
WHERE kupon_sonucu = 'KAZANDI'
ORDER BY kesinKazanc DESC, paylasilma_tarihi DESC
             */


            /*
            $data = $query['query']->select(
                'tb_kuponlar.*',
                'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id")
                ->join("tb_paylasimlar", "tb_paylasimlar.durum", "tb_kuponlar.kupon_id")
                ->where([
                    'kayitDurumu' => 1,
                    'paylasim_tipi' => 1])->get();

            return $data->toSql();

            /*
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];
//
            return Res::success(200, 'Posts', $result);
            */
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function population(){
        try{
            $data  = DB::select("SELECT tb_kullanicilar.adSoyad, tb_kullanicilar.IMG, tb_kullanicilar.kullaniciAdi, ((SELECT COUNT(tb_kuponlar.kupon_id) FROM tb_kuponlar WHERE tb_kuponlar.kupon_sahibi = tb_kullanicilar.ID AND tb_kuponlar.kupon_sonucu = 'KAZANDI')/(SELECT COUNT(tb_kuponlar.kupon_id) FROM tb_kuponlar WHERE tb_kuponlar.kupon_sahibi = tb_kullanicilar.ID AND (tb_kuponlar.kupon_sonucu = 'KAZANDI' OR tb_kuponlar.kupon_sonucu='KAYBETTI')))*100 AS yuzde FROM `tb_kullanicilar` WHERE `populer` = 1 ORDER BY `yuzde` DESC LIMIT 6");
            return Res::success(200, 'navigation tab menu sliders', $data);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }


    /*
     * $this->db->select("*");
		$this->db->select("((SELECT COUNT(tb_kuponlar.kupon_id) FROM tb_kuponlar WHERE tb_kuponlar.kupon_sahibi = tb_kullanicilar.ID AND tb_kuponlar.kupon_sonucu = 'KAZANDI')/(SELECT COUNT(tb_kuponlar.kupon_id) FROM tb_kuponlar WHERE tb_kuponlar.kupon_sahibi = tb_kullanicilar.ID AND (tb_kuponlar.kupon_sonucu = 'KAZANDI' OR tb_kuponlar.kupon_sonucu='KAYBETTI')))*100 AS yuzde");
		$this->db->where("populer", 1);
		$this->db->order_by("yuzde","DESC");
		return $this->db->get("tb_kullanicilar");
     */


/*
 *             $data = $query['query']->join("tb_paylasimlar", "tb_paylasimlar.kullanici_id", "tb_takip.takipEdilenID")
                ->select(
                    'tb_paylasimlar.created_at AS post_created_at',
                    'tb_paylasimlar.paylasim_id AS post_id',
                    'tb_paylasimlar.*',
                    'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasimlar.kullanici_id")
                ->where("tb_takip.takipEdenID", "=", $user_id);
 */


    /*
     * $this->db->select("*");
		$this->db->select("(SELECT COUNT(yorum_id) FROM tb_paylasim_yorumlari WHERE tb_paylasim_yorumlari.paylasim_id = tb_paylasimlar.paylasim_id) AS yorum_sayisi");
		if ($this->session->kullaniciOturum) {
			$this->db->select("(SELECT COUNT(begeni_id) FROM tb_begeni WHERE tb_begeni.begenen_id = " . $this->session->ID . " AND tb_begeni.paylasim_id = tb_paylasimlar.paylasim_id) AS begendi");
		} else {
			$this->db->select("0 as begendi");
		}
		$this->db->select("(SELECT COUNT(begeni_id) FROM tb_begeni WHERE tb_begeni.paylasim_id = tb_paylasimlar.paylasim_id) AS total_begendi");
		$this->db->where("tb_paylasimlar.paylasim_tipi", 2);
		$this->db->join("tb_kullanicilar", "tb_kullanicilar.ID = tb_paylasimlar.kullanici_id", "left");
		$this->db->join("tb_kuponlar", "tb_kuponlar.kupon_id = tb_paylasimlar.durum", "left");
		$this->db->where("tb_kuponlar.kupon_sonucu", "KAZANDI");
		$this->db->order_by("tb_kuponlar.kesinKazanc", "DESC");
		$this->db->limit(10);
//		echo $this->db->get_compiled_select("tb_paylasimlar");
		return $this->db->get("tb_paylasimlar");
     */


}

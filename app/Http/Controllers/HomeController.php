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
                    'paylasim_tipi' => 1])->get();
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

    public function coupons(Request $request){
        try {
            $query = TReq::multiple($request, Posts::class);
            $data = $query['query']->select(
                'tb_paylasimlar.*',
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

    public function population(){

    }

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

<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Libraries\Res;
use App\Libraries\TReq;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function getActivities(Request $request)
    {
        try {
            $query = TReq::multiple($request, Activities::class);
            $data = $query["query"]
                ->select("islemler.islem_turu", "islemler.islem_id", "islemler.islem_tarihi",'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->join("tb_takip", "tb_takip.takipEdilenID", "islemler.kullanici_id", "inner")
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "islemler.kullanici_id", "inner")
                ->where("tb_takip.takipEdenID", $request->user()->ID)
	            ->get();
            return Res::success(200, 'Activity', $data);
        } catch (\Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => [
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $message, $error);
        }
    }
}

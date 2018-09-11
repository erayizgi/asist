<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Follow;
use App\Libraries\Res;
use App\Libraries\TReq;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function getActivities(Request $request)
    {
        try {
            $query = TReq::multiple($request, Activities::class);
            $followings = Follow::where("takipEdenID",$request->user()->ID)->limit(5000)->get(); // THIS WILL BLOW UP
            $data = $query["query"]
                ->select("islemler.islem_turu", "islemler.islem_id", "islemler.islem_tarihi",'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
//                ->join("tb_takip", "tb_takip.takipEdilenID", "islemler.kullanici_id", "inner")
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "islemler.kullanici_id", "inner");
            foreach($followings as $f){
            	$data->orWhere("islemler.kullanici_id",$f->takipEdilenID);
            }
            $data->orWhere("islemler.kullanici_id",$request->user()->ID);
            $data->orderBy("islemler.islem_tarihi","DESC");
//            return $data->toSql();
            $data = $data->get();

//                ->where("tb_takip.takipEdenID", $request->user()->ID)
//                ->orWhere("tb_takip.takipEdilenID", $request->user()->ID)
//	            ->where("tb_takip.deleted_at",NULL)
				/*->orWhere("islemler.kullanici_id", $request->user()->ID)*/

//				->toSql();
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

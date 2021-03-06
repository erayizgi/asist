<?php

namespace App\Http\Controllers;

use App\News;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    //
    public function getNews(Request $request)
    {
        try{
            $query = TReq::multiple($request, News::class);
            $data = $query['query']->where('kayitDurumu', 1)->get();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'News',$result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function sideNews(Request $request){
        try{
            $query = TReq::multiple($request, News::class);
            $data = $query['query']
                ->select("tb_haberler.*","tb_kullanicilar.kullaniciAdi","tb_kullanicilar.IMG","tb_kullanicilar.adSoyad")
                ->join("tb_kullanicilar","tb_kullanicilar.ID","tb_haberler.kullaniciID")
                ->where(['haberKoseYazi' => 1, 'tb_haberler.kayitDurumu' => 1])->get();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'News',$result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function getSingle(Request $request, $slug)
    {
        try{
            $query = TReq::multiple($request, News::class);
            $data = $query['query']->where(['URL' => $slug, 'kayitDurumu' => 1])->first();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'News',$result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }
}

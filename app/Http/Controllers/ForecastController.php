<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use App\Events;
use App\Forecast;
use App\Libraries\Res;
use App\Libraries\TReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForecastController extends Controller
{
    /*
     * return $this->db->query('select tah.*,adSoyad,home,away,league_code,league_name,event_oid, adSoyad from tb_iddaa_tahminleri tah
 inner join tb_kullanicilar kull on tah.tahminci_id=kull.ID
 inner join events maclar on tah.mac_id=maclar.identifier_id
 order by mac_id desc limit 25 ')->result_array();
     */

    public function forecast(Request $request){
        try {
            $query = TReq::multiple($request, Forecast::class);
            $data  = $query['query']->select( 'events.event_id', 'events.home', 'events.away', 'events.league_code', 'events.league_name', 'events.event_oid')
                                    ->join('events', 'tb_iddaa_tahminleri.mac_id', 'events.identifier_id')
                                    ->orderBy('tb_iddaa_tahminleri.mac_id', 'DESC')->get();
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];
            return Res::success(200, 'Forecasts', $result);

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

    public function detail(Request $request, $id){
        try {
            $query = TReq::multiple($request, Events::class);
            $data = $query['query']->where('event_id', $id)->first();
            $result = [
                'metadata' => [
                    //'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data'     => $data,
                'comments' => Forecast::where(['mac_id' => $id, 'tahmin_durumu' => 1])->get(),
            ];

            return Res::success(200, 'Forecast Details', $result);
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

    public function create(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'mac_id'         => 'required',
                'tahmin_yorumu'  => 'required|filled|min:2',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $create = [
                'mac_id' => $request->mac_id,
                'tahminci_id' => $request->user()->ID,
                'tahmin_yorumu' => $request->tahmin_yorumu,
            ];

            if(!Forecast::create($create)){
                throw new Exception('Yorum Oluşturulurken Bir Hata Oluştu!', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200, 'success', 'Yorum Başarılı Bir Şekilde Oluşturuldu!');

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

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'tahmin_id' => 'required',
                'tahmin_yorumu' => 'required',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $update = Forecast::where([
                'tahmin_id'   => $request->tahmin_id,
                'tahminci_id' => $request->user()->ID,
            ])->update($request->all());

            if(!$update){
                throw new Exception('Yorum Düzenlenirken Bir Hata Oluştu!', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200, 'success', 'Yorum Başarılı Bir Şekilde Düzenlendi!');

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

    public function delete(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'tahmin_id' => 'required',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $delete = Forecast::where([
                'tahmin_id'   => $request->tahmin_id,
                'tahminci_id' => $request->user()->ID,
            ])->update([
                'tahmin_durumu' => 0
            ]);

            if(!$delete){
                throw new Exception('Yorum Silinirken Bir Hata Oluştu!', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200, 'success', 'Yorum Başarılı Bir Şekilde Silindi!');

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

    public function surveys(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                ''
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

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
}

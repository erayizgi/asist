<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use App\Events;
use App\Forecast;
use App\Libraries\Res;
use App\Libraries\TReq;
use App\ForecastSurveys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;

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
            $query = Treq::multiple($request, Forecast::class);
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
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function detail(Request $request, $id){
        try {

            $query = TReq::multiple($request, Events::class);
            /*
            $data  = $query['query']->select(
                'events.event_id',
                'events.home',
                'events.away',
                'events.league_code',
                'league_name', 'event_oid', 'identifier_id')->where('identifier_id',  $id)->first();
            */
            $data = DB::table('events')
                ->select('events.event_id', 'events.home', 'events.away', 'events.league_code')
                ->selectSub("SELECT odd FROM odd_options WHERE odd_options.event_id = events.event_id AND odd_options.odd_type_id = 1 AND odd_options.odd_option = 1", 'S1')
                ->selectSub("SELECT odd FROM odd_options WHERE odd_options.event_id = events.event_id AND odd_options.odd_type_id = 1 AND odd_options.odd_option = 'X'", 'SX')
                ->selectSub("SELECT odd FROM odd_options WHERE odd_options.event_id = events.event_id AND odd_options.odd_type_id = 1 AND odd_options.odd_option = 2", 'S2')
                ->where("identifier_id", "=", $id)
                ->first();
            $result = [
                'metadata' => [
                    //'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data'     => $data,
                'comments' => Forecast::select('tb_iddaa_tahminleri.*', 'tb_kullanicilar.IMG', 'tb_kullanicilar.adSoyad')->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_iddaa_tahminleri.tahminci_id")->where(['mac_id' => $id, 'tahmin_durumu' => 1])->get(),
            ];

            return Res::success(200, 'Forecast Details', $result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
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

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
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

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
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
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }


    public function surveys(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'mac_id'   => 'required',
                'yanit_id' => 'required',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $check = ForecastSurveys::where([
                'mac_id'       => $request->mac_id,
                'kullanici_id' => $request->user()->ID
            ])->count();

            $result = [
                'results'   => [
                    'option_one'   => ForecastSurveys::where(['mac_id' => $request->mac_id, 'yanit_id' => 1])->count(),
                    'option_two'   => ForecastSurveys::where(['mac_id' => $request->mac_id, 'yanit_id' => 2])->count(),
                    'option_three' => ForecastSurveys::Where(['mac_id' => $request->mac_id, 'yanit_id' => 3])->count(),
                ],
            ];

            if($check > 0){
                return Res::success(200, 'Daha Önce Bu Ankete Katılım Sağladınız!', $result);
            }else{
                $create = ForecastSurveys::insert([
                    'mac_id'       => $request->mac_id,
                    'yanit_id'     => $request->yanit_id,
                    'kullanici_id' => $request->user()->ID
                ]);

                if(!$create){
                    throw new Exception('Ankete Katılırken Bir Hata Oluştu!', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            $result = [
                'results'   => [
                    'option_one'   => ForecastSurveys::where(['mac_id' => $request->mac_id, 'yanit_id' => 1])->count(),
                    'option_two'   => ForecastSurveys::where(['mac_id' => $request->mac_id, 'yanit_id' => 2])->count(),
                    'option_three' => ForecastSurveys::Where(['mac_id' => $request->mac_id, 'yanit_id' => 3])->count(),
                ],
            ];

            return Res::success(200, 'Ankete Başarılı Bir Şekilde Katılınız!', $result);

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }


    public function check(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'mac_id' => 'required'
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $check = ForecastSurveys::where([
                'mac_id'       => $request->mac_id,
                'kullanici_id' => $request->user()->ID
            ])->count();

            if($check > 0){
                $result = [
                    'results'   => [
                        'option_one'   => ForecastSurveys::where(['mac_id' => $request->mac_id, 'yanit_id' => 1])->count(),
                        'option_two'   => ForecastSurveys::where(['mac_id' => $request->mac_id, 'yanit_id' => 2])->count(),
                        'option_three' => ForecastSurveys::Where(['mac_id' => $request->mac_id, 'yanit_id' => 3])->count(),
                    ],

                    'is_exists' => true
                ];
            }else{
                $result = [
                    'is_exists' => false
                ];
            }



        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

}

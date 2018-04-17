<?php

namespace App\Http\Controllers;

use Exception;
use App\Dictionary;
use App\Libraries\Res;
use App\Libraries\TReq;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    /*
     * try{
            $query = Treq::multiple($request, User::class);
            $data  = $query['query']->where(['kullaniciYetki' => 1, 'kayitDurumu' => 1])->get();

            $result = [
                'metadata' => [
                    'count' => 1,
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];

            return Res::success(200, 'Tippers', $result);
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
     */


    //
    public function index(Request $request){
        try{
            $query = Treq::multiple($request, Dictionary::class);
            $data  = $query['query']->where('kayit_durumu', 1)->get();

            $result = [
                'metadata' => [
                    'count'  => 1,
                    'offset' => $query['offset'],
                    'limit'  => $query['limit'],
                ],
                'data' => $data
            ];

            return Res::success(200, 'Dictionary', $result);

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

    public function detail(Request $request, $slug){
        try{
            $query = Treq::multiple($request, Dictionary::class);
            $data  = $query['query']->where(['sozluk_url' => $slug, 'kayit_durumu' => 1])->get();

            $result = [
                'metadata' => [
                    'count'  => 1,
                    'offset' => $query['offset'],
                    'limit'  => $query['limit'],
                ],
                'data' => $data
            ];

            return Res::success(200, 'Dictionary', $result);

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

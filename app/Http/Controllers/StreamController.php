<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Stream;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StreamController extends Controller
{
    //
    public function getStreams(Request $request)
    {
        try{
            $query = TReq::multiple($request, Stream::class);
            $data = $query['query']->where('kayitDurumu', 1)->get();
            $result = [
                'metadata'=>[
                    'count' =>$data->count(),
                    'offset'=>$query['offset'],
                    'limit' =>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'Streams',$result);
        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

    public function getStream(Request $request, $slug)
    {
        try{
            $query = TReq::multiple($request, Stream::class);
            $data = $query['query']->where(['URL' => $slug, 'kayitDurumu' => 1])->first();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'Stream',$result);
        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

    public function sendMessage(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'yayinID'         => 'required|filled',
                'mesajAciklamasi' => 'required|filled|min:3',
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Chat::create([
                'yayinID'         => $request->yayinID,
                'kullaniciID'     => $request->user()->ID,
                'mesajAciklamasi' => $request->mesajAciklamasi,
            ]);

            return Res::success(200,'Users', 'success');

        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }
}

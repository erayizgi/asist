<?php

namespace App\Http\Controllers;
use DB;
use App\Notifications;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationsController extends Controller
{
    public function notifications(Request $request)
    {
        try{
            $query = TReq::multiple($request, Notifications::class);
            $data = $query['query']->where('alici_id', $request->user()->ID)->get();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data' => $data
            ];

            return Res::success(200,'notifications',$result);
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

    public function read(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                'bildirim_id' => 'required',
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            $read = Notifications::where([
                'alici_id'    => $request->user()->ID,
                'bildirim_id' => $request->bildirim_id,
            ])->update([
                'okundu' => 1
            ]);

            if(!$read){
                throw new Exception($validator->errors(), 400);
            }

            return Res::success(200,'notifications', 'success');

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

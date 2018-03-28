<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\TReq;
use App\Libraries\Res;
class UserController extends Controller
{
    //
    public function create(Request $request)
    {
        return $request->user();
    }

    public function getUsers(Request $request)
    {
        try{
            $query = TReq::multiple($request, User::class);
            $data = $query['query']->get();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'Users',$result);
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
    public function patch(Request $request)
    {
        try{
            $user = $request->user();
            /*
             * $validator = Validator::make($request->all(), [
            'address_1' => 'required|filled|min:10',
            'address_2' => 'required|filled|min:10',
            'address_3' => 'required|filled|min:10',
            'postcode' => 'required|filled',
            'country_id' => 'required|filled|integer|exists:countries,country_id',
			'lat'=>'required|filled',
			'long'=>'required|filled'
        ]);
             */
            $adSoyad = $request->adSoyad;
            $validator = Validator::make($request->all(),[
                "adSoyad" => "required|filled|min:3"
            ]);
            if($validator->fails()){
                throw new Exception($validator->errors(),400);
            }
            $user = User::find($request->user()->ID)->update($request->all());
            if($user){
                $response = [
                    'status'=>true,
                    'code'=>200,
                    'message'=>"Kullanıcı bilgileri düzenlendi",
                    'data'=>$user
                ];
                return response()->json($response,$response["code"]);
            }
        }catch (Exception $e){
            $response = [
                'status'=>false,
                'code'=>$e->getCode(),
                'message'=>$e->getMessage()
            ];
            return $e;
        }
    }
}

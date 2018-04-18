<?php

namespace App\Http\Controllers;

use App\Follow;
use App\User;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FollowController extends Controller
{
    public function follow(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
               'kullanici_id' => "required"
            ]);
            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }
            $user = User::where("kullaniciAdi",$request->kullanici_id)->first();

            $follow = Follow::insert([
                'takipEdenID'   => $request->user()->ID,
                'takipEdilenID' => $user->ID
            ]);

            if(!$follow){
                throw new Exception("Bad Request", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200,'follow', 'success');


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

    public function unfollow(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
               'kullanici_id' => 'required'
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }
            $user = User::where("kullaniciAdi",$request->kullanici_id)->first();

            $unfollow = Follow::where([
                'takipEdenID'   => $request->user()->ID,
                'takipEdilenID' => $user->ID
            ])->delete();

            if(!$unfollow){
                throw new Exception("Bad Request", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200,'unfollow', 'success');

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

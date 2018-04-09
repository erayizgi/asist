<?php

namespace App\Http\Controllers;

use App\Follow;
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
               'takipEdilenID' => $request->kullanici_id
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            $follow = Follow::insert([
                'takipEdenID'   => $request->user()->ID,
                'takipEdilenID' => $request->kullanici_id
            ]);

            if(!$follow){
                throw new Exception($validator->errors(), 400);
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
                throw new Exception($validator->errors(), 400);
            }

            $unfollow = Follow::where([
                'takipEdenID'   => $request->user()->ID,
                'takipEdilenID' => $request->kullanici_id
            ])->delete();

            if(!$unfollow){
                throw new Exception($validator->errors(), 400);
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

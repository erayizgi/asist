<?php

namespace App\Http\Controllers;

use App\Follow;
use App\User;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;

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

			if($request->user()->ID === $user->ID){
				throw new Exception('Kendi Kendinizi Takip Edemezsiniz!', Response::HTTP_FORBIDDEN);
			}

            $check = Follow::where([
            	'takipEdenID' => $request->user()->ID,
				'takipEdilenID' => $user->ID
			])->count();

            if($check > 0){
            	throw new Exception('Seçmiş Olduğunuz Kullanıcı Takip Ediyorsunuz', Response::HTTP_BAD_REQUEST);
			}

            $follow = Follow::insert([
                'takipEdenID'   => $request->user()->ID,
                'takipEdilenID' => $user->ID
            ]);

            if(!$follow){
                throw new Exception("Bad Request", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200,'follow', 'success');

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
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

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }
}

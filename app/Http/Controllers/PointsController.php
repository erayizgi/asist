<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use App\Users;
use App\Points;
use App\Libraries\Res;
use App\Libraries\TReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
class PointsController extends Controller
{
    public function create(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'amount'         => 'required',
                'operation_id'   => 'required',
                'operation_type' => 'required',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $create = Points::create([
                'user_id'        => $request->user()->ID,
                'amount'         => $request->amount,
                'operation_id'   => $request->operation_id,
                'operation_type' => $request->operation_type,
            ]);

            if(!$create){
                throw new exception("Puan Eklenirken Bir Hata Oluştu!", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200, "success", "Puan Başarılı Bir Şekilde Oluşturuldu!");

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }


    public function all(Request $request){
        try{

            $point = [];

            $validator = Validator::make($request->all(), [
                'amount' => 'required|filled',
                'operation_id' => 'required|filled',
                'operation_type' => 'required|filled'
            ]);

            if($validator->fails()){
                throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
            }

            $users = User::select('ID')->where('kayitDurumu', 1)->get();

            foreach($users as $user){
                $point[] = [
                    'user_id' => $user['ID'],
                    'amount' => $request->amount,
                    'operation_id' => $request->operation_id,
                    'operation_type' => $request->operation_type
                ];
            }

            Points::create($point);

            return Res::success(200, "success", "Tüm Kullanıcılara Puan Başarılı Bir Şekilde Oluşturuldu!");
        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function update(Request $request){
        try{
            $validator = Validator::make($request->all(),[
               'point_id'       => 'required',
               'amount'         => 'required|filled',
               'operation_id'   => 'required|filled',
               'operation_type' => 'required|filled',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }

            $update = Points::find($request->point_id)->update($request->only('amount', 'operation_id', 'operation_type'));

            if(!$update){
                throw new exception('Puan Düzenlenirken Bir Hata Oluştu!', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200, "success", "Puan Başarılı Bir Şekilde Düzenlendi!");

        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }
}

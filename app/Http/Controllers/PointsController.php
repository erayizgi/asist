<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use App\Points;
use App\Libraries\Res;
use App\Libraries\TReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        }catch(Exception $e){
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

        }catch(Exception $e){
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

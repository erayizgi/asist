<?php

namespace App\Http\Controllers;

use App\Posts;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\TReq;
use App\Libraries\Res;

class PostsController extends Controller
{
    //
    public function posts(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'paylasim_tipi' => 'required|filled',
                'durum'         => 'required|filled',
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            Posts::create([
                'paylasim_tipi' => $request->paylasim_tipi,
                'durum'         => $request->durum,
                'kullanici_id'  => $request->user()->ID
            ]);
            return Res::success(200,'Posts', 'new post has been created successfully');

        }catch(Exception $e){
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

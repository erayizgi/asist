<?php

namespace App\Http\Controllers;

use Exception;
use App\Sliders;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;

class SlidersController extends Controller
{
    public function nav(Request $request){
        try{
            $query = Treq::multiple($request, Sliders::class);
            $data  = $query['query']->select('*')->where([
                'position' => 1
            ])->get();

            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];
            return Res::success(200, 'Sliders', $result);
        }catch (Exception $e) {
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

    public function header(Request $request){
        try{
            $query = Treq::multiple($request, Sliders::class);
            $data  = $query['query']->select('*')->where([
                'position' => 2
            ])->get();

            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];
            return Res::success(200, 'Sliders', $result);
        }catch (Exception $e) {
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

    public function video(Request $request){
        try{
            $query = Treq::multiple($request, Sliders::class);
            $data  = $query['query']->select('*')->where([
                'position' => 3
            ])->get();

            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];
            return Res::success(200, 'Sliders', $result);
        }catch (Exception $e) {
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

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
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
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
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
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
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }
}

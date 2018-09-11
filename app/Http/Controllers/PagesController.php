<?php

namespace App\Http\Controllers;

use App\Page;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;

class PagesController extends Controller
{
    //
    public function index(Request $request)
    {
        try {

            $query = TReq::multiple($request, Page::class);
            $data = $query['query']->where('kayitDurumu', 1)->get();

            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ], 'data' => $data
            ];

            return Res::success(200, 'Sayfalar', $result);

        } catch (Exception $e) {

        }
    }

    public function detail(Request $request, $slug)
    {
        try {

            $query = TReq::multiple($request, Page::class);
            $data = $query['query']->where('URL', $slug)->first();

            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ], 'data' => $data
            ];

            return Res::success(200, 'Sayfa Detay', $result);

        } catch (Exception $e) {

        }
    }
}

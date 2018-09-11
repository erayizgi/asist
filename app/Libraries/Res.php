<?php
/**
 * Created by PhpStorm.
 * User: erayizgi
 * Date: 28.03.2018
 * Time: 13:03
 */

namespace App\Libraries;

use Illuminate\Http\Request;

class Res
{
    public static function success($code = 200, $message = 'Request is successfull!', $data = null)
    {
        $response = [
            'status' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];

        return response()->json($response, $code);
    }

    public static function fail($code = 404, $message = 'Not found!', $data = null)
    {
        $code = $code > 500 ? 500: $code;
        $code = $code < 200 ? 500: $code;
        $response = [
            'status' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];
        return response()->json($response, $code);
    }
}

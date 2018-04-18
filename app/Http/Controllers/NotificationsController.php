<?php

namespace App\Http\Controllers;

use DB;
use App\Notifications;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotificationsController extends Controller
{
    public function notifications(Request $request)
    {
        try {
            $query = TReq::multiple($request, Notifications::class);
            $data = $query['query']
                ->select("bildirimler.*", "tb_kullanicilar.kullaniciAdi", "tb_kullanicilar.adSoyad", "tb_kullanicilar.IMG")
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "olusturan_id", "inner")
                ->where('alici_id', $request->user()->ID)->where("okundu", 0)->get();
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
            ];

            return Res::success(200, 'notifications', $result);
        } catch (Exception $e) {
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

    public function read(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bildirim_id' => 'required',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
            }

            $read = Notifications::where([
                'alici_id' => $request->user()->ID,
                'bildirim_id' => $request->bildirim_id,
            ])->update([
                'okundu' => 1
            ]);

            if (!$read) {
                throw new Exception($validator->errors(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Res::success(200, 'notifications', 'success');

        } catch (ValidationException $e) {
            return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }
}

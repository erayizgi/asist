<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Likes;
use DB;
use App\Comments;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller
{
    public function select(Request $request, $post)
    {
        try {
            $query = TReq::multiple($request, Comments::class);
            $data = $query["query"]
                ->select(
                    'tb_paylasim_yorumlari.*',
                    'tb_paylasim_yorumlari.created_at as yorum_yapilan_tarih',
                    'tb_kullanicilar.adSoyad', 'tb_kullanicilar.IMG', 'tb_kullanicilar.kullaniciAdi')
                ->where('paylasim_id', $post)
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasim_yorumlari.kullanici_id");
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data->get()
            ];

            return Res::success(200, 'Users', $result);
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

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'yorum' => 'required|filled',
                'icerik_tipi' => 'required|filled',
                'paylasim_id' => 'required|filled',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $create = Comments::create([
                'yorum' => $request->yorum,
                'kullanici_id' => $request->user()->ID,
                'paylasim_id' => $request->paylasim_id,
                'icerik_tipi' => $request->icerik_tipi, // post yorumu için 1 haber yorumu için 2
            ]);
            $comment = Comments::select(
                "tb_paylasim_yorumlari.yorum",
                "tb_paylasim_yorumlari.created_at as yorum_tarihi",
                "tb_kullanicilar.IMG",
                "tb_kullanicilar.adSoyad",
                "tb_kullanicilar.kullaniciAdi"
            )
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_paylasim_yorumlari.kullanici_id")
                ->where("tb_paylasim_yorumlari.yorum_id", $create->yorum_id)
                ->first();
            Activities::create([
                "kullanici_id" => $create->kullanici_id,
                "islem_turu" => "comment",
                "islem_id" => $create->yorum_id,
                "islem_tarihi" => date("Y-m-d H:i:s"),
            ]);

            if (!$create) {
                throw new Exception($validator->errors(), 400);
            }

            return Res::success(200, 'comments', $comment);

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

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'yorum' => 'required|filled',
                'yorum_id' => 'required|filled',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $update = Comments::where([
                'yorum_id' => $request->yorum_id,
                'kullanici_id' => $request->user()->ID,
            ])->update([
                'yorum' => $request->yorum
            ]);

            if (!$update) {
                throw new Exception($validator->errors(), 400);
            }

            return res::success(200, 'comment', 'success');

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

    public function delete(Request $request,$yorum_id)
    {
        try {
            if (!Comments::where(['kullanici_id' => $request->user()->ID, 'yorum_id' => $yorum_id])->delete()) {
                throw new Exception('an error', 400);
            }
            return Res::success(200, 'success', 'success');
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
}

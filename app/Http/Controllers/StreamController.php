<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Stream;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;

class StreamController extends Controller
{
    //
    public function getStreams(Request $request)
    {
        try{
            $query = TReq::multiple($request, Stream::class);
            $data = $query['query']->where('kayitDurumu', 1)->get();
            $result = [
                'metadata'=>[
                    'count' =>$data->count(),
                    'offset'=>$query['offset'],
                    'limit' =>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'Streams',$result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function getStream(Request $request, $slug)
    {
        try{
            $query = TReq::multiple($request, Stream::class);
            $data = $query['query']->where(['URL' => $slug, 'kayitDurumu' => 1])->first();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'Stream',$result);
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function sendMessage(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'yayinID'         => 'required|filled',
                'mesajAciklamasi' => 'required|filled|min:3',
            ]);

            if($validator->fails()){
                throw new ValidationException($validator,Response::HTTP_BAD_REQUEST,$validator->errors());
            }
            $yayin = Stream::where("ID",$request->yayinID)->where("canliYayin",1)->get();
            if($yayin->count() === 0){
                throw new Exception("Canlı olmayan yayına mesaj gönderemezsiniz",Response::HTTP_FORBIDDEN);
            }
            Chat::create([
                'yayinID'         => $request->yayinID,
                'kullaniciID'     => $request->user()->ID,
                'mesajAciklamasi' => $request->mesajAciklamasi,
            ]);

            return Res::success(200,'Chat', 'success');
        } catch (ValidationException $e){
            return Res::fail($e->getResponse(),$e->getMessage(),$e->errors());
        } catch (Exception $e) {
            return Res::fail($e->getCode(), $e->getMessage());
        }
    }

    public function getMessage(Request $request, $id)
    {
        try{
            $query = TReq::multiple($request, Chat::class);
            $data = $query['query']->select("tb_sohbetler.*", "tb_kullanicilar.kullaniciAdi", "tb_kullanicilar.adSoyad", "tb_kullanicilar.IMG")
                ->join("tb_kullanicilar", "tb_kullanicilar.ID", "kullaniciID", "inner")
                ->where('yayinID', $id)->get()->toArray();
            $chat = [];
            foreach($data as $k=>$v){
                $chat[] = array_pop($data);
            }
            $result = [
                'metadata' => [
                    'count' => count($data),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $chat
            ];

            return Res::success(200, 'Chat', $result);
        } catch (Exception $e) {
            return Res::fail(500, $e->getMessage());
        }
    }
}

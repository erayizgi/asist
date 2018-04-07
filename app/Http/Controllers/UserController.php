<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\TReq;
use App\Libraries\Res;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
class UserController extends Controller
{
    //

    public function me(Request $request)
    {
        try {
            $query = TReq::multiple($request, User::class);
            $data = $query['query']->find($request->user()->ID);
            $result = [
                'metadata' => [
                    'count' => 1,
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
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

    public function statistics(Request $request)
    {
        try {
            $query = TReq::multiple($request, User::class);
                        $result = [
                'metadata' => [
                    'count' => 1,
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'followers' => DB::table('tb_takip')->where('takipEdilenID', $request->user()->ID)->count(),
                'following' => DB::table('tb_takip')->where('takipEdenID', $request->user()->ID)->count(),
                'comments'  => DB::table('tb_paylasim_yorumlari')->where('kullanici_id', $request->user()->ID)->count(),
                'posts'     => DB::table('tb_paylasimlar')->where(['kullanici_id' => $request->user()->ID, 'kayit_durumu' => 1])->count()
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



    public function getUsers(Request $request)
    {
        try {
            $query = TReq::multiple($request, User::class);
            $data = $query['query']->get();
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
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

    public function getUser(Request $request, $username)
    {
        try {
            $query = TReq::multiple($request, User::class);
            $data = $query['query']->where('kullaniciAdi', $username)->first();
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data,
                'followers' => DB::table('tb_takip')->where('takipEdilenID', $data->ID)->count(),
                'following' => DB::table('tb_takip')->where('takipEdenID', $data->ID)->count(),
                'comments'  => DB::table('tb_paylasim_yorumlari')->where('kullanici_id', $data->ID)->count(),
                'posts'     => DB::table('tb_paylasimlar')->where(['kullanici_id' => $data->ID, 'kayit_durumu' => 1])->count(),
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

    public function searchUser(Request $request, $username)
    {
        try {
            $query = TReq::multiple($request, User::class);
            $data = $query['query']->where('kullaniciAdi', $username)->first();
            $result = [
                'metadata' => [
                    'count' => $data->count(),
                    'offset' => $query['offset'],
                    'limit' => $query['limit'],
                ],
                'data' => $data
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

    public function post(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kullaniciAdi' => 'required|filled|unique:tb_kullanicilar,kullaniciAdi',
                'password' => 'required|filled|min:3',
                'adSoyad' => 'required|filled|min:3',
                'email' => 'required|filled|unique:tb_kullanicilar,email',
                'kullaniciTelefon' => 'required|filled|unique:tb_kullanicilar,kullaniciTelefon',
                'kullaniciBulunduguUlke' => 'required|filled|min:3',
                'kullaniciBulunduguSehir' => 'required|filled|min:3'
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }
            $data = $request->only(['kullaniciAdi','password','adSoyad','email','kullaniciTelefon','kullaniciBulunduguUlke','kullaniciBulunduguSehir']);
            $data["password"] = bcrypt($data["password"]);
            if (User::create($data)) {
                return Res::success(200, 'Users', 'user account has been created successfully');
            } else {
                throw new Exception('user is not successfully created', 400);
            }
        } catch (Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => json_decode($e->getMessage())

            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $message, $error);
        }
    }

    public function image(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'url'  => 'required',
                'type' => 'required',
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            $image = [
                ($request->type == 'avatar' ? 'IMG' : 'coverIMG') => $request->url,
            ];

            if(User::find($request->user()->ID)->update($image)){
                return Res::success(200, 'Users', User::find($request->user()->ID));
            } else {
                throw new Exception('user is not successfully created', 400);
            }

        } catch (Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => json_decode($e->getMessage())
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $message, $error);
        }
    }

    public function reset(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6'
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            $password = bcrypt($request->password);

            if(User::find($request->user()->ID)->update(['password' => $password])){
                return Res::success(200, 'Users', 'user account password has been updated successfully');
            } else {
                throw new Exception('user is not successfully created', 400);
            }

        } catch (Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => json_decode($e->getMessage())
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $message, $error);
        }
    }

    public function forgot(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required'
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            if(!$user = User::where('email', $request->email)->first()){
                throw new Exception('error', 400);
            }

            $gsm  = str_replace(['()', ')', '-'], '', $user->kullaniciTelefon);
            $pass = substr(md5(uniqid(mt_rand(), true)), 0, 8);
            $text = "AsistAnaliz Kullanıcı Parolanız: ".$pass;

            $client = new Client();

            if(!$client->request('GET', "http://facetahmin.e-panelim.com/Gonder.aspx?Site=FT&Tur=SMS&Tel='+$gsm+'&Icerik=$text")){
                throw new Exception('sms error', 400);
            }

            if(!User::find($user->ID)->update(['password' => bcrypt($pass)])){
                throw new Exception('error', 400);
            }

            return Res::success(200, 'Users', 'sms ok');


        } catch (Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => json_decode($e->getMessage())
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $message, $error);
        }
    }

    public function patch(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'adSoyad' => 'required|filled|min:3',
                'kullaniciHakkinda' => 'required|filled|min:3',
                'kullaniciDogumTarihi' => 'required|filled|',
                'kullaniciBulunduguUlke' => 'required|filled|min:5',
                'kullaniciBulunduguSehir' => 'required|filled|min:5'
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }
            $data = $request->only(['adSoyad', 'kullaniciHakkinda', 'kullaniciDogumTarihi', 'kullaniciBulunduguUlke', 'kullaniciBulunduguSehir']);
            $data["kullaniciDogumTarihi"] = date("Y-m-d",strtotime($data["kullaniciDogumTarihi"]));
            if (User::find($request->user()->ID)->update($data)) {
                return Res::success(200, 'Users', User::find($request->user()->ID));
            } else {
                throw new Exception('user is not successfully created', 400);
            }
        } catch (Exception $e) {
            $error = new \stdClass();
            $error->errors = [
                'exception' => json_decode($e->getMessage())
            ];
            $message = 'An error has occured!';
            return Res::fail($e->getCode(), $message, $error);
        }

        /*
        try{
            $user = $request->user();

             * $validator = Validator::make($request->all(), [
            'address_1' => 'required|filled|min:10',
            'address_2' => 'required|filled|min:10',
            'address_3' => 'required|filled|min:10',
            'postcode' => 'required|filled',
            'country_id' => 'required|filled|integer|exists:countries,country_id',
			'lat'=>'required|filled',
			'long'=>'required|filled'
        ]);

            $adSoyad = $request->adSoyad;
            $validator = Validator::make($request->all(),[
                "adSoyad" => "required|filled|min:3"
            ]);
            if($validator->fails()){
                throw new Exception($validator->errors(),400);
            }
            $user = User::find($request->user()->ID)->update($request->all());
            if($user){
                $response = [
                    'status'=>true,
                    'code'=>200,
                    'message'=>"Kullanıcı bilgileri düzenlendi",
                    'data'=>$user
                ];
                return response()->json($response,$response["code"]);
            }
        }catch (Exception $e){
            $response = [
                'status'=>false,
                'code'=>$e->getCode(),
                'message'=>$e->getMessage()
            ];
            return $e;
        }
        */
    }
}

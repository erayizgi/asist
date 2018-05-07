<?php

namespace App\Http\Controllers;

use App\Follow;
use App\Points;
use DB;
use App\User;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use App\Libraries\TReq;
use App\Libraries\Res;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class UserController extends Controller
{
	public function tippers(Request $request)
	{
		try {
			$query = Treq::multiple($request, User::class);
			$data = $query['query']
				->select('ID', 'IMG', 'adSoyad', 'kullaniciAdi', 'kullaniciHakkinda')
				->where(['kayitDurumu' => 1, 'kullaniciYetki' => 1])
				->inRandomOrder()->get();

			$tippers = [];

			foreach ($data as $k => $v) {
				$tippers[$k] = [
					'ID' => $data[$k]['ID'],
					'IMG' => $data[$k]['IMG'],
					'adSoyad' => $data[$k]['adSoyad'],
					'kullaniciAdi' => $data[$k]['kullaniciAdi'],
					'kullaniciHakkinda' => $data[$k]['kullaniciHakkinda'],
					'followers' => DB::table('tb_takip')->where('takipEdilenID', $data[$k]['ID'])->count(),
					'following' => DB::table('tb_takip')->where('takipEdenID', $data[$k]['ID'])->count(),
					'coupons' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $data[$k]['ID']])->count(),
					'won' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $data[$k]['ID'], 'kupon_sonucu' => 'KAZANDI'])->count(),
					'lose' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $data[$k]['ID'], 'kupon_sonucu' => 'KAYBETTI'])->count(),

				];
			}


			$result = [
				'metadata' => [
					'count' => 1,
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $tippers
			];

			return Res::success(200, 'Tippers', $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function following(Request $request, $username)
	{
		try {
			$user = User::where("kullaniciAdi", $username)->first();
			if (!$user) {
				throw new Exception("Kullanıcı bulunamadı", 404);
			}
			$data = Follow::where("takipEdenID", $user->ID)
				->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_takip.takipEdilenID", "inner")
				->get();

			$result = [
				'metadata' => [
				],
				'data' => $data
			];

			/*$following = Follow::where("takipEdenID", $user->ID)
				->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_takip.takipEdilenID", "inner")
				->get();
			*/
			return Res::success(200, "Takip edilenler", $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function followers(Request $request, $username)
	{
		try {
			$user = User::where("kullaniciAdi", $username)->first();
			if (!$user) {
				throw new Exception("Kullanıcı bulunamadı", 404);
			}
			$query = Treq::multiple($request, Follow::class);
			$data = $query['query']->where("takipEdilenID", $user->ID)
				->join("tb_kullanicilar", "tb_kullanicilar.ID", "tb_takip.takipEdenID", "inner")
				->get();
			$result = [
				'metadata' => [
					'count' => 1,
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $data
			];
			return Res::success(200, "Takip edenler", $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	/*
	public function following(Request $request, $username)
	{
		try {

			$findUser = User::select('kullaniciAdi', 'ID')->where('kullaniciAdi', $username)->first();

			if (!$findUser) {
				throw new Exception('Böyle Bir Kullanıcı Bulunamadı!', Response::HTTP_BAD_REQUEST);
			}

			$isFollowing = Follow::select('tb_kullanicilar.ID', 'tb_kullanicilar.IMG', 'tb_kullanicilar.adSoyad', 'tb_kullanicilar.kullaniciHakkinda')
				->join('tb_kullanicilar', 'tb_kullanicilar.ID', 'tb_takip.takipEdilenID', 'inner')
				->where("takipEdenID", $findUser->ID)
				->get();

			return Res::success(200, "Kullanıcını Takip Ettiği Profiller", $isFollowing);

		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}

	}

	public function followers(Request $request, $username)
	{
		try {

			$findUser = User::select('kullaniciAdi', 'ID')->where('kullaniciAdi', $username)->first();

			if (!$findUser) {
				throw new Exception('Böyle Bir Kullanıcı Bulunamadı!', Response::HTTP_BAD_REQUEST);
			}

			$query = Treq::multiple($request, Follow::class);
			$data = $query['query']->select('tb_kullanicilar.ID', 'tb_kullanicilar.IMG', 'tb_kullanicilar.adSoyad', 'tb_kullanicilar.kullaniciHakkinda')
				->where("takipEdilenID", $findUser->ID)
				->join('tb_kullanicilar', 'tb_kullanicilar.ID', 'tb_takip.takipEdenID', 'inner')
				->get();


			$result = [
				'metadata' => [
					'count' => 1,
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $data
			];

			return Res::success(200, "Takip edenler", $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}
	*/

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
			return Res::fail($e->getCode(), $e->getMessage());
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
				'comments' => DB::table('tb_paylasim_yorumlari')->where('kullanici_id', $request->user()->ID)->count(),
				'posts' => DB::table('tb_paylasimlar')->where(['kullanici_id' => $request->user()->ID, 'kayit_durumu' => 1])->count(),
				'coupons' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $request->user()->ID])->count(),
				'won' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $request->user()->ID, 'kupon_sonucu' => 'KAZANDI'])->count(),
				'lose' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $request->user()->ID, 'kupon_sonucu' => 'KAYBETTI'])->count(),
				'balance' => DB::table('tb_points')->where(['user_id' => $request->user()->ID])->sum('amount')
			];

			return Res::success(200, 'Users', $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function balance(Request $request)
	{
		try {
			$balance = DB::table('tb_points')->where(['user_id' => $request->user()->ID])->sum('amount');
			$result = [
				'balance' => $balance
			];
			return Res::success(200, "Balance", $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
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
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function getUser(Request $request, $username)
	{
		try {
			$query = TReq::multiple($request, User::class);
			$data = $query['query']->where('kullaniciAdi', $username)->first();
			$result = [
				'metadata' => [
					//'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
				'data' => $data,
				'followers' => DB::table('tb_takip')->where('takipEdilenID', $data->ID)->count(),
				'following' => DB::table('tb_takip')->where('takipEdenID', $data->ID)->count(),
				'comments' => DB::table('tb_paylasim_yorumlari')->where('kullanici_id', $data->ID)->count(),
				'posts' => DB::table('tb_paylasimlar')->where(['kullanici_id' => $data->ID, 'kayit_durumu' => 1])->count(),
				'coupons' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $data['ID']])->count(),
				'won' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $data['ID'], 'kupon_sonucu' => 'KAZANDI'])->count(),
				'lose' => DB::table('tb_kuponlar')->where(['kupon_sahibi' => $data['ID'], 'kupon_sonucu' => 'KAYBETTI'])->count(),
				'balance' => DB::table('tb_points')->where(['user_id' => $data->ID])->sum('amount')
			];

			return Res::success(200, 'Users', $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function searchUser(Request $request, $username)
	{
		try {
			$query = TReq::multiple($request, User::class);
			//$data = $query['query']->where('kullaniciAdi', $username)->first();

			$data = $query['query']->select('IMG', 'kullaniciAdi', 'adSoyad', 'kullaniciHakkinda')
				->where('kayitDurumu', 1)
				->where(function ($query) use ($username) {
					$query->where('kullaniciAdi', 'LIKE', '%' . $username . '%')
						->orWhere('adSoyad', 'LIKE', '%' . $username . '%');
				})
				->get();
//				->toSql();
//			return $data;

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
			return Res::fail($e->getCode(), $e->getMessage());
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

			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}

			$data = $request->only(['kullaniciAdi', 'password', 'adSoyad', 'email', 'kullaniciTelefon']);
			$data['password'] = bcrypt($data['password']);

			$user = User::create($data)->ID;

			$follow = DB::table('otomatik_takip')->get();


			$follower = [];

			foreach ($follow as $k => $v) {
				$follower[] = [
					'takipEdenID' => $user,
					'takipEdilenID' => $follow[$k]->otakip_edilen
				];
			}

			$point = Points::create([
				'user_id' => $user,
				'amount' => 50,
				'operation_type' => 'KAYIT',
				'operation_id' => 0
			]);

			$auto = Follow::insert($follower);

			return Res::success(200, 'Users', 'Kullanıcı Kaydı Başarılı Bir Şekilde Oluşturuldu!');

		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}


		/*
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
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}
			$data = $request->only(['kullaniciAdi', 'password', 'adSoyad', 'email', 'kullaniciTelefon', 'kullaniciBulunduguUlke', 'kullaniciBulunduguSehir']);
			$data["password"] = bcrypt($data["password"]);
			if (User::create($data)) {
				return Res::success(200, 'Users', 'user account has been created successfully');
			} else {
				throw new Exception('user is not successfully created', Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}*/
	}

	public function image(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'url' => 'required',
				'type' => 'required',
			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}

			$image = [
				($request->type == 'avatar' ? 'IMG' : 'coverIMG') => $request->url,
			];

			if (User::find($request->user()->ID)->update($image)) {
				return Res::success(200, 'Users', User::find($request->user()->ID));
			} else {
				throw new Exception('user is not successfully created', Response::HTTP_INTERNAL_SERVER_ERROR);
			}

		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function reset(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'password' => 'required|min:6'
			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}

			$password = bcrypt($request->password);

			if (User::find($request->user()->ID)->update(['password' => $password])) {
				return Res::success(200, 'Users', 'user account password has been updated successfully');
			} else {
				throw new Exception('user is not successfully created', Response::HTTP_INTERNAL_SERVER_ERROR);
			}

		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function forgot(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'email' => 'required'
			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}

			if (!$user = User::where('email', $request->email)->first()) {
				throw new Exception('error', Response::HTTP_INTERNAL_SERVER_ERROR);
			}

			$gsm = str_replace(['()', ')', '-'], '', $user->kullaniciTelefon);
			$pass = substr(md5(uniqid(mt_rand(), true)), 0, 8);
			$text = "AsistAnaliz Kullanıcı Parolanız: " . $pass;

			$client = new Client();

			if (!$client->request('GET', "http://facetahmin.e-panelim.com/Gonder.aspx?Site=FT&Tur=SMS&Tel='+$gsm+'&Icerik=$text")) {
				throw new Exception('sms error', Response::HTTP_INTERNAL_SERVER_ERROR);
			}

			if (!User::find($user->ID)->update(['password' => bcrypt($pass)])) {
				throw new Exception('error', Response::HTTP_INTERNAL_SERVER_ERROR);
			}

			return Res::success(200, 'Users', 'sms ok');


		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
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
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}
			$data = $request->only(['adSoyad', 'kullaniciHakkinda', 'kullaniciDogumTarihi', 'kullaniciBulunduguUlke', 'kullaniciBulunduguSehir']);
			$data["kullaniciDogumTarihi"] = date("Y-m-d", strtotime($data["kullaniciDogumTarihi"]));
			if (User::find($request->user()->ID)->update($data)) {
				return Res::success(200, 'Users', User::find($request->user()->ID));
			} else {
				throw new Exception('user is not successfully created', Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
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

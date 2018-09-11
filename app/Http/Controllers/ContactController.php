<?php

namespace App\Http\Controllers;

use Exception;
use App\Contact;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
	public function index(Request $request)
	{
		try {

			$validator = Validator::make($request->all(), [
				'name' => 'required|filled',
				'email' => 'required|filled',
				'subject' => 'required|filled',
				'message' => 'required|filled',
			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}

			if (!Contact::create($request->all())) {
				throw new Exception('İletişim Maili Oluşturulurken Bir Hata Oluştu!', Response::HTTP_INTERNAL_SERVER_ERROR);
			};

			return Res::sucess('İletişim Kaydınız Başarılı Bir Şekilde Oluşturuldu!', Response::HTTP_OK);

		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}
}

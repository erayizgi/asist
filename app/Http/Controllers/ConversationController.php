<?php

namespace App\Http\Controllers;

use App\Conversation;
use App\Message;
use App\Notifications;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;


class ConversationController extends Controller
{
	public function inbox(Request $request)
	{
		try {
			$query = TReq::multiple($request, Conversation::class);
			$data = $query['query']
				->where('receiver_id', $request->user()->ID)
				->get();
			$result = [
				'metadata' => [
					'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
			];

			foreach ($data as $conversation) {
				$result['conversations'][] = [
					"conversation" => $conversation,
					"message" => Message::where('conversation_id', $conversation['conversation_id'])
						->orderBy("created_at", "DESC")
						->get()
				];

			}

			return Res::success(200, 'inbox', $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function outbox(Request $request)
	{
		try {
			$query = TReq::multiple($request, Conversation::class);
			$data = $query['query']->where('sender_id', $request->user()->ID)->get();
			$result = [
				'metadata' => [
					'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
			];


			foreach ($data as $conversation) {
				$result['conversations'][] = [
					"conversation" => $conversation,
					"message" => Message::where('conversation_id', $conversation['conversation_id'])->get()
				];
			}

			return Res::success(200, 'outbox', $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function create(Request $request)
	{
		// If conversation has been created successfully create a new message
		try {
			$validator = Validator::make($request->all(), [
				'receiver_id' => 'required|filled',
				'message' => 'required|filled|min:1'
			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}

			$conversation_id = Conversation::create([
				'sender_id' => $request->user()->ID,
				'receiver_id' => $request->receiver_id,
			]);


			if (!Message::create(['conversation_id' => $conversation_id->conversation_id, 'user_id' => $request->user()->ID, 'content' => $request->message])) {
				throw new Exception("conversation error", Response::HTTP_INTERNAL_SERVER_ERROR);
			}

			$notification = [
				"alici_id" => $request->receiver_id,
				"bildirim_tipi" => 'pm',
				"bildirim_url" => "/message/inbox/".$conversation_id->conversation_id,
				"olusturan_id" => $request->user()->ID
			];

			if (!Notifications::insert($notification)) {
				throw new Exception('notification errors', Response::HTTP_INTERNAL_SERVER_ERROR);
			}

			return Res::success(200, 'pm', 'success');

		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function reply(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'message' => 'required|filled|min:1',
				'conversation_id' => 'required|filled',
			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}
			Message::create(['conversation_id' => $request->conversation_id, 'user_id' => $request->user()->ID, 'content' => $request->message]);
			return Res::success(200, 'pm', 'success');
		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function read(Request $request, $id)
	{
		try {
			$query = TReq::multiple($request, Conversation::class);
			$data = $query['query']->where('conversation_id', $id)->get();
			$result = [
				'metadata' => [
					'count' => $data->count(),
					'offset' => $query['offset'],
					'limit' => $query['limit'],
				],
			];
			foreach ($data as $conversation) {
				$result['conversations'] = [
					"conversation" => $conversation,
					"message" => Message::where('conversation_id', $conversation['conversation_id'])->get()
				];
			}
			Conversation::find($id)->update(["is_read" => 1]);
			return Res::success(200, 'Conversations', $result);
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

	public function delete(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'conversation_id' => 'required'
			]);

			if ($validator->fails()) {
				throw new ValidationException($validator, Response::HTTP_BAD_REQUEST, $validator->errors());
			}


			if (!Conversation::find($request->conversation_id)->delete()) {
				throw new Exception('an error', Response::HTTP_INTERNAL_SERVER_ERROR);
			}

			return Res::success(200, 'success', 'success');

		} catch (ValidationException $e) {
			return Res::fail($e->getResponse(), $e->getMessage(), $e->errors());
		} catch (Exception $e) {
			return Res::fail($e->getCode(), $e->getMessage());
		}
	}

}

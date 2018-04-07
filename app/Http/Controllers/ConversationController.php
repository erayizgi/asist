<?php

namespace App\Http\Controllers;

use App\Conversation;
use App\Message;
use Exception;
use App\Libraries\TReq;
use App\Libraries\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    public function inbox(Request $request){
        try{
            $query = TReq::multiple($request, Conversation::class);
            $data = $query['query']->where('receiver_id', $request->user()->ID)->get();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
            ];

            foreach($data as $conversation)
            {
                $result['conversations'] = $conversation;
                $result['conversations']['message'] = Message::where('conversation_id', $conversation['conversation_id'])->get();
            }

            return Res::success(200,'inbox',$result);
        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

    public function outbox(Request $request){
        try{
            $query = TReq::multiple($request, Conversation::class);
            $data = $query['query']->where('sender_id', $request->user()->ID)->get();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
            ];


            foreach($data as $conversation)
            {
                $result['conversations'] = $conversation;
                $result['conversations']['message'] = Message::where('conversation_id', $conversation['conversation_id'])->get();
            }

            return Res::success(200,'outbox',$result);
        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

    public function create(Request $request){
        // If conversation has been created successfully create a new message
        try{
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|filled',
                'message'     => 'required|filled|min:1'
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            $conversation_id = Conversation::create([
                'sender_id'    => $request->user()->ID,
                'receiver_id'  => $request->receiver_id,
            ]);


            if(Message::create(['conversation_id' => $conversation_id->conversation_id, 'user_id' => $request->user()->ID, 'content' => $request->message])) {
                return Res::success(200,'pm', 'success');
            }else {
                throw new Exception('user is not successfully created', 400);
            }

        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

    public function reply(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'message'         => 'required|filled|min:1',
                'conversation_id' => 'required|filled',
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }

            if(Message::create(['conversation_id' => $request->conversation_id, 'user_id' => $request->user()->ID, 'content' => $request->message])){
                return Res::success(200,'pm', 'success');
            }else {
                throw new Exception('an error', 400);
            }
        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

    public function read(Request $request, $id){
        try{
            $query = TReq::multiple($request, Conversation::class);
            $data = $query['query']->where('conversation_id', $id)->first();
            $result = [
                'metadata'=>[
                    'count'=>$data->count(),
                    'offset'=>$query['offset'],
                    'limit'=>$query['limit'],
                ],
                'data'=>$data
            ];

            return Res::success(200,'Conversations',$result);
        }catch (Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

    public function delete(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'conversation_id' => 'required'
            ]);

            if($validator->fails()){
                throw new Exception($validator->errors(), 400);
            }


            if(!Conversation::find($request->conversation_id)->delete()){
                throw new Exception('an error', 400);
            }

            return Res::success(200,'success', 'success');


        }catch(Exception $e){
            $error = new \stdClass();
            $error->errors = [
                'exception'=>[
                    $e->getMessage()
                ]
            ];
            $message = 'An error has occured!';
            return Res::fail(500,$message,$error);
        }
    }

}

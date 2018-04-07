<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = "tb_message";
    protected $primaryKey = "message_id";

    protected $fillable = ['conversation_id', 'user_id', 'content'];
}

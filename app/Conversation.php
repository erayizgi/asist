<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Conversation extends Model
{
    use SoftDeletes;
    protected $table = "tb_conversation";
    protected $primaryKey = "conversation_id";
    protected $dates = ['deleted_at'];
    protected $fillable = ['sender_id', 'receiver_id'];
}

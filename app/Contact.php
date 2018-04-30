<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
	protected $primaryKey = "contact_id";
	protected $table = "contact";
	protected $fillable = ["contact_id", "email", "name", "subject", "message", "created_at", "updated_at"];

}

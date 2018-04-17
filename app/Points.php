<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Points extends Model
{
    use SoftDeletes;
    protected $table = "tb_points";
    protected $primaryKey = "point_id";
    protected $dates = ['deleted_at'];
    protected $fillable = ['user_id', 'amount', 'operation_type', 'operation_id'];

}

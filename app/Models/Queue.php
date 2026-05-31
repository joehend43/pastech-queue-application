<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $guarded = ["id"];
    protected $dates = [
        'called_at',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'called_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}

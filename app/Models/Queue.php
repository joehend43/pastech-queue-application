<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Queue extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];
    protected $dates = [
        'called_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $casts = [
        'called_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : null;
    }

    protected function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : null;
    }

    protected function getDeletedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : null;
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessKeyStatistic extends Model
{
    protected $casts = [
        'data' => 'array'
    ];

    public function accessKey()
    {
        return $this->belongsTo(AccessKey::class);
    }
}

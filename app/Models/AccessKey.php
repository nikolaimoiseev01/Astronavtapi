<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccessKey extends Model
{
    protected $casts = [
        'expires_at' => 'datetime'
    ];
    public function partner(): belongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function statistics(): hasMany
    {
        return $this->hasMany(AccessKeyStatistic::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Partner extends Model
{
    public function accessKeys(): hasOne
    {
        return $this->hasOne(AccessKey::class);
    }
}

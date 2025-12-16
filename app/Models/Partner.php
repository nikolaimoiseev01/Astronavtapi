<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Partner extends Model
{
    use Notifiable;
    public function accessKeys(): hasOne
    {
        return $this->hasOne(AccessKey::class);
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}

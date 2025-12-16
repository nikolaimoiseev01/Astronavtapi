<?php

namespace App\Filament\Resources\AccessKeys\Pages;

use App\Filament\Resources\AccessKeys\AccessKeyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccessKey extends CreateRecord
{
    protected static string $resource = AccessKeyResource::class;
}

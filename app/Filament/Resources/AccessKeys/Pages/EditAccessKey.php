<?php

namespace App\Filament\Resources\AccessKeys\Pages;

use App\Filament\Resources\AccessKeys\AccessKeyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccessKey extends EditRecord
{
    protected static string $resource = AccessKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

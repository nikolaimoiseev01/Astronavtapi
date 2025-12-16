<?php

namespace App\Filament\Resources\AccessKeys\Pages;

use App\Filament\Resources\AccessKeys\AccessKeyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccessKeys extends ListRecords
{
    protected static string $resource = AccessKeyResource::class;
    protected static ?string $title = 'API Ключи партнеров';

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}

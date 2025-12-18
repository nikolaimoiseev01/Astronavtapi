<?php

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Resources\Partners\PartnerResource;
use App\Notifications\PartnerCreatedNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPartner extends EditRecord
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('Послать Email')
                ->requiresConfirmation()
                ->successNotificationTitle('Email отправлен')
                ->action(function(Model $record) {
                    $this->record->notify(
                        new PartnerCreatedNotification($record->accessKey)
                    );
                })
        ];
    }
}

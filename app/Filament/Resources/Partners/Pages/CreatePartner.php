<?php

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Resources\Partners\PartnerResource;
use App\Models\AccessKey;
use Filament\Resources\Pages\CreateRecord;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;
    protected static ?string $title = 'Создать партнера';

    protected function afterCreate(): void
    {
        AccessKey::create([
            'name' => "Ключ для {$this->record->name}",
            'key' => hash('sha256', $this->record->name . config('app.key')),
            'partner_id' => $this->record->id,
            'expires_at' => now()->addMonth()
        ]);
    }
}

<?php

namespace App\Filament\Resources\AccessKeys\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccessKeyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required(),
                     DateTimePicker::make('expires_at')
                         ->label('Срок действия')
                        ->required(),
                    Select::make('partner_id')
                        ->label('Партнер')
                        ->required()
                        ->disabled()
                        ->relationship(name: 'partner', titleAttribute: 'name'),
                    TextInput::make('key')
                        ->label('Ключ')
                        ->disabled(),
                ])->columnSpanFull()->columns(2)
            ]);
    }
}

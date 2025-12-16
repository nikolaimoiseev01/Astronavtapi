<?php

namespace App\Filament\Resources\AccessKeys;

use App\Filament\Resources\AccessKeys\Pages\CreateAccessKey;
use App\Filament\Resources\AccessKeys\Pages\EditAccessKey;
use App\Filament\Resources\AccessKeys\Pages\ListAccessKeys;
use App\Filament\Resources\AccessKeys\Schemas\AccessKeyForm;
use App\Filament\Resources\AccessKeys\Tables\AccessKeysTable;
use App\Models\AccessKey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccessKeyResource extends Resource
{
    protected static ?string $model = AccessKey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;
    protected static ?string $navigationLabel = 'API Ключи партнеров';

    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AccessKeyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccessKeysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccessKeys::route('/'),
            'create' => CreateAccessKey::route('/create'),
            'edit' => EditAccessKey::route('/{record}/edit'),
        ];
    }
}

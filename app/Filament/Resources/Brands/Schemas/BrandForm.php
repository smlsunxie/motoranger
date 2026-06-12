<?php

namespace App\Filament\Resources\Brands\Schemas;

use App\Enums\VehicleType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('廠牌名稱')
                    ->required(),
                Select::make('type')
                    ->label('類型')
                    ->options(VehicleType::class)
                    ->default('motorcycle')
                    ->required(),
                TextInput::make('homepage')
                    ->label('官網'),
                Textarea::make('description')
                    ->label('說明')
                    ->columnSpanFull(),
            ]);
    }
}

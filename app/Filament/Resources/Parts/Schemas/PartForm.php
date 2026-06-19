<?php

namespace App\Filament\Resources\Parts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->label('店家')
                    ->relationship('store', 'name'),
                TextInput::make('sku')
                    ->label('料號'),
                TextInput::make('name')
                    ->label('品名')
                    ->required(),
                TextInput::make('spec')
                    ->label('規格'),
                TextInput::make('unit')
                    ->label('單位')
                    ->required()
                    ->default('個'),
                TextInput::make('cost')
                    ->label('成本')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('price')
                    ->label('售價')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('stock_qty')
                    ->label('庫存')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('image_path')
                    ->label('圖片')
                    ->image(),
                Textarea::make('description')
                    ->label('說明')
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('店名')
                    ->required(),
                TextInput::make('phone')
                    ->label('電話')
                    ->tel(),
                TextInput::make('mobile')
                    ->label('手機'),
                TextInput::make('fax')
                    ->label('傳真'),
                TextInput::make('email')
                    ->label('Email')
                    ->email(),
                TextInput::make('address')
                    ->label('地址'),
                TextInput::make('tax_id')
                    ->label('統一編號'),
                Textarea::make('description')
                    ->label('說明')
                    ->columnSpanFull(),
                FileUpload::make('logo_path')
                    ->label('Logo')
                    ->image()
                    ->disk('public')
                    ->directory('logos'),
            ]);
    }
}

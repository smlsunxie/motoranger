<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('姓名')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('密碼')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state)),
                Select::make('store_id')
                    ->label('所屬店家')
                    ->relationship('store', 'name'),
                Select::make('role')
                    ->label('角色')
                    ->options(UserRole::class)
                    ->default('operator')
                    ->required(),
                TextInput::make('phone')
                    ->label('電話')
                    ->tel(),
            ]);
    }
}

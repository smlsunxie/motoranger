<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('姓名')
                    ->required(),
                TextInput::make('mobile')
                    ->label('手機')
                    ->tel(),
                TextInput::make('phone')
                    ->label('市話')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email')
                    ->email(),
                TextInput::make('address')
                    ->label('地址'),
                DatePicker::make('birthday')
                    ->label('生日'),
                TextInput::make('line_id')
                    ->label('LINE ID'),
                Select::make('store_id')
                    ->label('所屬店家')
                    ->relationship('store', 'name'),
                Textarea::make('description')
                    ->label('內部備注')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}

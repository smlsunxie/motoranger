<?php

namespace App\Filament\Resources\StoreExpenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StoreExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('store_id')
                    ->label('店家')
                    ->relationship('store', 'name'),
                TextInput::make('title')
                    ->label('項目')
                    ->required(),
                TextInput::make('amount')
                    ->label('金額')
                    ->required()
                    ->numeric(),
                DatePicker::make('date')
                    ->label('日期')
                    ->default(today())
                    ->required(),
                Textarea::make('description')
                    ->label('說明')
                    ->columnSpanFull(),
            ]);
    }
}

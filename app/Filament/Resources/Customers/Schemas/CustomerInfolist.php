<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('客戶資料')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('姓名'),
                        TextEntry::make('mobile')->label('手機')->placeholder('—'),
                        TextEntry::make('phone')->label('市話')->placeholder('—'),
                        TextEntry::make('email')->label('Email')->placeholder('—'),
                        TextEntry::make('address')->label('地址')->placeholder('—'),
                        TextEntry::make('line_id')->label('LINE ID')->placeholder('—'),
                        TextEntry::make('birthday')->label('生日')->date('Y-m-d')->placeholder('—'),
                        TextEntry::make('repair_orders_count')
                            ->label('維修次數')
                            ->state(fn ($record) => $record->repairOrders()->count()),
                        TextEntry::make('description')->label('內部備註')->columnSpanFull()->placeholder('—'),
                    ]),
            ]);
    }
}

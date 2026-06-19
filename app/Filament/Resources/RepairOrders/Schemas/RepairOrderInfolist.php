<?php

namespace App\Filament\Resources\RepairOrders\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RepairOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $money = fn ($state): string => '$ '.number_format((int) $state);

        return $schema
            ->components([
                Section::make('基本資料')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_no')->label('單號'),
                        TextEntry::make('status')->label('狀態')->badge(),
                        TextEntry::make('date')->label('進廠日期')->date('Y-m-d'),
                        TextEntry::make('vehicle.plate_no')->label('車牌'),
                        TextEntry::make('customer.name')->label('客戶')->placeholder('—'),
                        TextEntry::make('user.name')->label('承辦技師')->placeholder('—'),
                        TextEntry::make('mileage')->label('進廠里程')->numeric()->suffix(' km'),
                        TextEntry::make('note')->label('維修描述 / 客戶反映')->columnSpanFull()->placeholder('—'),
                    ]),

                Section::make('維修項目')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(12)
                            ->schema([
                                TextEntry::make('name')->label('項目')->columnSpan(5),
                                TextEntry::make('type')->label('類型')->badge()->columnSpan(2),
                                TextEntry::make('qty')->label('數量')->columnSpan(2),
                                TextEntry::make('price')->label('單價')->formatStateUsing($money)->columnSpan(3),
                            ]),
                    ]),

                Section::make('金額')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('subtotal')->label('小計')->formatStateUsing($money),
                        TextEntry::make('discount')->label('折扣')->formatStateUsing($money),
                        TextEntry::make('total')->label('應收金額')->formatStateUsing($money)->weight('bold'),
                        TextEntry::make('balance')->label('未收')->state(fn ($record) => $record->balance)->formatStateUsing($money),
                    ]),

                Section::make('現場照片')
                    ->visible(fn ($record) => $record->photos()->exists())
                    ->schema([
                        ImageEntry::make('photos.path')
                            ->hiddenLabel()
                            ->disk('public')
                            ->state(fn ($record) => $record->photos->pluck('path')->all()),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\RepairOrders\Tables;

use App\Enums\RepairOrderStatus;
use App\Models\RepairOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RepairOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('order_no')
                    ->label('單號')
                    ->searchable(),
                TextColumn::make('date')
                    ->label('進廠日期')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('客戶')
                    ->searchable(),
                TextColumn::make('vehicle.plate_no')
                    ->label('車牌')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('狀態')
                    ->badge(),
                TextColumn::make('total')
                    ->label('應收')
                    ->money('TWD', divideBy: 1)
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('已收')
                    ->money('TWD', divideBy: 1)
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('未收')
                    ->money('TWD', divideBy: 1)
                    ->color(fn (RepairOrder $record) => $record->balance > 0 ? 'danger' : 'success'),
                TextColumn::make('user.name')
                    ->label('技師')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('狀態')
                    ->options(RepairOrderStatus::class),
                SelectFilter::make('store_id')
                    ->label('店家')
                    ->relationship('store', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('估價單')
                    ->icon('heroicon-o-printer')
                    ->url(fn (RepairOrder $record) => route('repair-orders.quote', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Parts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label('店家')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('料號')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('品名')
                    ->searchable(),
                TextColumn::make('spec')
                    ->label('規格')
                    ->searchable(),
                TextColumn::make('unit')
                    ->label('單位')
                    ->searchable(),
                TextColumn::make('cost')
                    ->label('成本')
                    ->money('TWD', divideBy: 1)
                    ->sortable(),
                TextColumn::make('price')
                    ->label('售價')
                    ->money('TWD', divideBy: 1)
                    ->sortable(),
                TextColumn::make('stock_qty')
                    ->label('庫存')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image_path')
                    ->label('圖片'),
                TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('刪除時間')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
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

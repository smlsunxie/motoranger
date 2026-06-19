<?php

namespace App\Filament\Resources\Vehicles\Tables;

use App\Enums\VehicleType;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate_no')
                    ->label('車牌')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('車主')
                    ->searchable(),
                TextColumn::make('customer.mobile')
                    ->label('車主手機')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('類型')
                    ->badge(),
                TextColumn::make('brand.name')
                    ->label('廠牌'),
                TextColumn::make('model')
                    ->label('車型')
                    ->searchable(),
                TextColumn::make('mileage')
                    ->label('里程')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('repair_orders_count')
                    ->label('維修次數')
                    ->counts('repairOrders'),
                TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('類型')
                    ->options(VehicleType::class),
                SelectFilter::make('brand_id')
                    ->label('廠牌')
                    ->relationship('brand', 'name'),
                TrashedFilter::make(),
            ])
            ->recordUrl(fn (Vehicle $record) => VehicleResource::getUrl('profile', ['record' => $record]))
            ->recordActions([
                Action::make('view')
                    ->label('檢視')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Vehicle $record) => VehicleResource::getUrl('profile', ['record' => $record])),
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

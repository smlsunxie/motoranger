<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Enums\VehicleType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    protected static ?string $title = '車輛';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plate_no')
                    ->label('車牌號碼')
                    ->required(),
                Select::make('type')
                    ->label('類型')
                    ->options(VehicleType::class)
                    ->default(VehicleType::Motorcycle)
                    ->required(),
                Select::make('brand_id')
                    ->label('廠牌')
                    ->relationship('brand', 'name'),
                TextInput::make('model')
                    ->label('車型'),
                TextInput::make('year')
                    ->label('年份')
                    ->numeric(),
                TextInput::make('cc')
                    ->label('排氣量 (cc)')
                    ->numeric(),
                TextInput::make('color')
                    ->label('顏色'),
                TextInput::make('mileage')
                    ->label('里程 (km)')
                    ->numeric()
                    ->default(0),
                TextInput::make('vin')
                    ->label('車身號碼'),
                TextInput::make('engine_no')
                    ->label('引擎號碼'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate_no')->label('車牌')->searchable(),
                TextColumn::make('type')->label('類型')->badge(),
                TextColumn::make('brand.name')->label('廠牌'),
                TextColumn::make('model')->label('車型'),
                TextColumn::make('mileage')->label('里程')->numeric(),
                TextColumn::make('repair_orders_count')->label('維修次數')->counts('repairOrders'),
            ])
            ->headerActions([
                CreateAction::make()->label('新增車輛'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

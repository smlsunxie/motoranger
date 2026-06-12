<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Enums\VehicleType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('車輛資料')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('customer_id')
                            ->label('車主')
                            ->relationship('customer', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim("{$record->name} {$record->mobile}"))
                            ->searchable(['name', 'mobile'])
                            ->preload()
                            ->required(),
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
                            ->label('出廠年份')
                            ->numeric(),
                        TextInput::make('cc')
                            ->label('排氣量 (cc)')
                            ->numeric(),
                        TextInput::make('color')
                            ->label('顏色'),
                        TextInput::make('mileage')
                            ->label('最新里程 (km)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('vin')
                            ->label('車身號碼'),
                        TextInput::make('engine_no')
                            ->label('引擎號碼'),
                        Textarea::make('description')
                            ->label('備注')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('車輛照片')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        Repeater::make('photos')
                            ->hiddenLabel()
                            ->relationship()
                            ->grid(3)
                            ->defaultItems(0)
                            ->addActionLabel('新增照片')
                            ->schema([
                                FileUpload::make('path')
                                    ->hiddenLabel()
                                    ->image()
                                    ->disk('public')
                                    ->directory('vehicle-photos')
                                    ->imageEditor()
                                    ->required(),
                                TextInput::make('caption')->label('說明'),
                                Hidden::make('user_id')->default(Auth::id()),
                            ]),
                    ]),
            ]);
    }
}

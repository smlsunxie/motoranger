<?php

namespace App\Filament\Resources\RepairOrders\Schemas;

use App\Enums\RepairItemType;
use App\Enums\RepairOrderStatus;
use App\Models\Part;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RepairOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基本資料')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('customer_id')
                            ->label('客戶')
                            ->relationship('customer', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim("{$record->name} {$record->mobile}"))
                            ->searchable(['name', 'mobile', 'phone'])
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('vehicle_id', null))
                            ->createOptionForm([
                                TextInput::make('name')->label('姓名')->required(),
                                TextInput::make('mobile')->label('手機'),
                                TextInput::make('address')->label('地址'),
                            ]),
                        Select::make('vehicle_id')
                            ->label('車輛')
                            ->options(fn (Get $get) => Vehicle::query()
                                ->where('customer_id', $get('customer_id'))
                                ->get()
                                ->pluck('display_name', 'id'))
                            ->getOptionLabelUsing(fn ($value) => Vehicle::find($value)?->display_name)
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($vehicle = Vehicle::find($state)) {
                                    $set('mileage', $vehicle->mileage);
                                }
                            })
                            ->createOptionForm([
                                Hidden::make('customer_id'),
                                TextInput::make('plate_no')->label('車牌號碼')->required(),
                                Select::make('brand_id')->label('廠牌')->relationship('brand', 'name'),
                                TextInput::make('model')->label('車型'),
                            ])
                            ->createOptionUsing(function (array $data, Get $get) {
                                $data['customer_id'] = $get('customer_id');

                                return Vehicle::create($data)->getKey();
                            }),
                        Select::make('user_id')
                            ->label('承辦技師')
                            ->relationship('user', 'name')
                            ->default(Auth::id()),
                        Select::make('status')
                            ->label('狀態')
                            ->options(RepairOrderStatus::class)
                            ->default(RepairOrderStatus::Quote)
                            ->required(),
                        DatePicker::make('date')
                            ->label('進廠日期')
                            ->default(today())
                            ->required(),
                        TextInput::make('mileage')
                            ->label('進廠里程 (km)')
                            ->numeric()
                            ->default(0),
                        Textarea::make('note')
                            ->label('維修描述 / 客戶反映')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('維修項目')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('sort')
                            ->addActionLabel('新增項目')
                            ->defaultItems(1)
                            ->columns(12)
                            ->live()
                            ->schema([
                                Select::make('part_id')
                                    ->label('零件')
                                    ->options(fn () => Part::orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($part = Part::find($state)) {
                                            $set('name', $part->name);
                                            $set('price', $part->price);
                                            $set('cost', $part->cost);
                                            $set('type', RepairItemType::Part->value);
                                        }
                                    })
                                    ->columnSpan(2),
                                Select::make('type')
                                    ->label('類型')
                                    ->options(RepairItemType::class)
                                    ->default(RepairItemType::Part)
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('name')
                                    ->label('項目名稱')
                                    ->required()
                                    ->columnSpan(3),
                                TextInput::make('qty')
                                    ->label('數量')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('price')
                                    ->label('單價')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->columnSpan(2),

                                TextInput::make('cost')
                                    ->label('成本')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('金額')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Placeholder::make('subtotal_preview')
                            ->label('小計')
                            ->content(fn (Get $get) => '$ '.number_format(self::itemsSubtotal($get))),
                        TextInput::make('discount')
                            ->label('折扣')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true),
                        Placeholder::make('total_preview')
                            ->label('應收金額')
                            ->content(fn (Get $get) => '$ '.number_format(
                                max(0, self::itemsSubtotal($get) - (int) $get('discount'))
                            )),
                    ]),

                Section::make('現場照片')
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
                                    ->directory('repair-photos')
                                    ->imageEditor()
                                    ->required(),
                                TextInput::make('caption')->label('說明'),
                                Hidden::make('user_id')->default(Auth::id()),
                            ]),
                    ]),
            ]);
    }

    protected static function itemsSubtotal(Get $get): int
    {
        return collect($get('items') ?? [])
            ->sum(fn ($item) => (int) ($item['qty'] ?? 0) * (int) ($item['price'] ?? 0));
    }
}

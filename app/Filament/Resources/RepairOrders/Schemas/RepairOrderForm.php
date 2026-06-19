<?php

namespace App\Filament\Resources\RepairOrders\Schemas;

use App\Enums\RepairItemType;
use App\Enums\RepairOrderStatus;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Part;
use App\Models\Vehicle;
use App\Support\PhotoUpload;
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
use Illuminate\Database\Eloquent\Builder;
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
                        Hidden::make('customer_id'),
                        Select::make('vehicle_id')
                            ->label('車輛 / 車號')
                            ->columnSpan(2)
                            ->searchable()
                            ->required()
                            ->live()
                            ->getSearchResultsUsing(fn (string $search) => Vehicle::query()
                                ->with(['brand', 'customer'])
                                ->where('plate_no', 'like', "%{$search}%")
                                ->orWhereHas('customer', fn (Builder $q) => $q
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('mobile', 'like', "%{$search}%"))
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Vehicle $v) => [$v->id => self::vehicleLabel($v)])
                                ->all())
                            ->getOptionLabelUsing(fn ($value) => self::vehicleLabel(Vehicle::with(['brand', 'customer'])->find($value)))
                            ->afterStateUpdated(function (Set $set, $state) {
                                $vehicle = Vehicle::find($state);
                                $set('customer_id', $vehicle?->customer_id);
                                $set('mileage', $vehicle?->mileage ?? 0);
                            })
                            ->createOptionForm([
                                TextInput::make('plate_no')->label('車牌號碼')->required(),
                                Select::make('customer_id')
                                    ->label('車主')
                                    ->helperText('可不填,之後再補登。')
                                    ->options(fn () => Customer::orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('name')->label('姓名')->required(),
                                        TextInput::make('mobile')->label('手機'),
                                        TextInput::make('address')->label('地址'),
                                    ])
                                    ->createOptionUsing(fn (array $data) => Customer::create($data)->getKey()),
                                Select::make('brand_id')
                                    ->label('廠牌')
                                    ->options(fn () => Brand::orderBy('name')->pluck('name', 'id')),
                                TextInput::make('model')->label('車型'),
                            ])
                            ->createOptionUsing(fn (array $data) => Vehicle::create($data)->getKey()),
                        Placeholder::make('customer_display')
                            ->label('車主')
                            ->content(function (Get $get) {
                                $customer = Customer::find($get('customer_id'));

                                return $customer ? trim("{$customer->name} {$customer->mobile}") : '—';
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
                            // 零件類型但未挑既有零件 → 依「項目名稱」自動建立 / 沿用零件
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => self::autoCreatePart($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data) => self::autoCreatePart($data))
                            ->schema([
                                Select::make('part_id')
                                    ->label('零件')
                                    ->helperText('找不到零件?輸入名稱後點「建立」即可新增。')
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
                                    ->createOptionForm([
                                        TextInput::make('name')->label('零件名稱')->required(),
                                        TextInput::make('price')->label('售價')->numeric()->default(0),
                                        TextInput::make('cost')->label('成本')->numeric()->default(0),
                                    ])
                                    ->createOptionUsing(fn (array $data) => Part::create([
                                        'name' => $data['name'],
                                        'price' => (int) ($data['price'] ?? 0),
                                        'cost' => (int) ($data['cost'] ?? 0),
                                        'store_id' => Auth::user()?->store_id,
                                    ])->getKey())
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

                                PhotoUpload::make('item_photos')
                                    ->label('項目照片')
                                    ->columnSpanFull()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (FileUpload $component, $record) {
                                        $component->state($record?->photos()->pluck('path')->all() ?? []);
                                    })
                                    ->saveRelationshipsUsing(function ($record, array $state) {
                                        $paths = array_values($state);
                                        $record->photos()->whereNotIn('path', $paths)->delete();
                                        $existing = $record->photos()->pluck('path')->all();
                                        foreach (array_diff($paths, $existing) as $path) {
                                            $record->photos()->create(['path' => $path, 'user_id' => Auth::id()]);
                                        }
                                    }),
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
                        PhotoUpload::make('photo_uploads')
                            ->hiddenLabel()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (FileUpload $component, $record) {
                                $component->state(
                                    $record?->photos()->pluck('path')->all() ?? []
                                );
                            })
                            ->saveRelationshipsUsing(function ($record, array $state) {
                                $paths = array_values($state);
                                $record->photos()->whereNotIn('path', $paths)->delete();
                                $existing = $record->photos()->pluck('path')->all();
                                foreach (array_diff($paths, $existing) as $path) {
                                    $record->photos()->create([
                                        'path' => $path,
                                        'user_id' => Auth::id(),
                                    ]);
                                }
                            }),
                    ]),
            ]);
    }

    /** 車輛選單顯示:車牌 廠牌 車型 / 車主 */
    protected static function vehicleLabel(?Vehicle $vehicle): ?string
    {
        if (! $vehicle) {
            return null;
        }

        $label = trim("{$vehicle->plate_no} {$vehicle->brand?->name} {$vehicle->model}");

        return $vehicle->customer ? "{$label} / {$vehicle->customer->name}" : $label;
    }

    /** 零件類型且未選既有零件時,依名稱自動建立 / 沿用零件並回填 part_id */
    protected static function autoCreatePart(array $data): array
    {
        $type = $data['type'] ?? RepairItemType::Part->value;
        $type = $type instanceof RepairItemType ? $type->value : $type;
        $isPart = $type === RepairItemType::Part->value;

        if (blank($data['part_id'] ?? null) && $isPart && filled($data['name'] ?? null)) {
            $data['part_id'] = Part::firstOrCreate(
                ['name' => $data['name']],
                [
                    'price' => (int) ($data['price'] ?? 0),
                    'cost' => (int) ($data['cost'] ?? 0),
                    'store_id' => Auth::user()?->store_id,
                ],
            )->getKey();
        }

        return $data;
    }

    protected static function itemsSubtotal(Get $get): int
    {
        return collect($get('items') ?? [])
            ->sum(fn ($item) => (int) ($item['qty'] ?? 0) * (int) ($item['price'] ?? 0));
    }
}

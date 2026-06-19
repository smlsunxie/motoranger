<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Enums\RepairItemType;
use App\Enums\RepairOrderStatus;
use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Part;
use App\Support\PhotoUpload;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Auth;

/**
 * 車輛維修檔案頁:車輛資訊 + 每次維修紀錄。
 * Header action 可建立新維修紀錄或重拍車輛圖。
 */
class VehicleProfile extends Page
{
    use InteractsWithRecord;

    protected static string $resource = VehicleResource::class;

    protected string $view = 'filament.resources.vehicles.pages.vehicle-profile';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return trim("{$this->record->plate_no} 維修檔案");
    }

    /** 該車所有維修紀錄(新到舊) */
    public function getOrders()
    {
        return $this->record->repairOrders()
            ->with(['items', 'user', 'store', 'photos'])
            ->latest('date')
            ->latest('id')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createOrder')
                ->label('建立維修紀錄')
                ->icon('heroicon-o-plus')
                ->modalHeading('建立維修紀錄')
                ->modalSubmitActionLabel('建立')
                ->schema([
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
                        ->default(fn () => $this->record->mileage),
                    Repeater::make('items')
                        ->label('維修項目')
                        ->addActionLabel('新增項目')
                        ->defaultItems(0)
                        ->columns(12)
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
                                ->columnSpan(3),
                            Select::make('type')
                                ->label('類型')
                                ->options(RepairItemType::class)
                                ->default(RepairItemType::Part)
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('name')->label('項目名稱')->required()->columnSpan(3),
                            TextInput::make('qty')->label('數量')->numeric()->default(1)->minValue(1)->columnSpan(2),
                            TextInput::make('price')->label('單價')->numeric()->default(0)->columnSpan(2),
                            PhotoUpload::make('photos')->label('項目照片')->columnSpanFull(),
                        ]),
                    Textarea::make('note')->label('維修描述 / 客戶反映')->rows(2),
                    PhotoUpload::make('photos')->label('現場照片'),
                ])
                ->action(function (array $data) {
                    $order = $this->record->repairOrders()->create([
                        'store_id' => Auth::user()?->store_id,
                        'customer_id' => $this->record->customer_id,
                        'user_id' => Auth::id(),
                        'status' => $data['status'],
                        'date' => $data['date'],
                        'mileage' => (int) ($data['mileage'] ?? 0),
                        'note' => $data['note'] ?? null,
                    ]);

                    foreach (array_values($data['items'] ?? []) as $i => $item) {
                        if (blank($item['name'] ?? null)) {
                            continue;
                        }

                        $type = $item['type'] ?? RepairItemType::Part->value;
                        $type = $type instanceof RepairItemType ? $type->value : $type;
                        $partId = $item['part_id'] ?? null;

                        // 零件類型且未挑既有零件 → 依名稱自動建立 / 沿用零件
                        if (blank($partId) && $type === RepairItemType::Part->value) {
                            $partId = Part::firstOrCreate(
                                ['name' => $item['name']],
                                [
                                    'price' => (int) ($item['price'] ?? 0),
                                    'cost' => (int) ($item['cost'] ?? 0),
                                    'store_id' => Auth::user()?->store_id,
                                ],
                            )->getKey();
                        }

                        $orderItem = $order->items()->create([
                            'part_id' => $partId,
                            'type' => $type,
                            'name' => $item['name'],
                            'qty' => (int) ($item['qty'] ?? 1),
                            'price' => (int) ($item['price'] ?? 0),
                            'cost' => (int) ($item['cost'] ?? 0),
                            'sort' => $i,
                        ]);

                        foreach ($item['photos'] ?? [] as $path) {
                            $orderItem->photos()->create(['path' => $path, 'user_id' => Auth::id()]);
                        }
                    }

                    foreach ($data['photos'] ?? [] as $path) {
                        $order->photos()->create(['path' => $path, 'user_id' => Auth::id()]);
                    }

                    if ((int) ($data['mileage'] ?? 0) > (int) $this->record->mileage) {
                        $this->record->update(['mileage' => (int) $data['mileage']]);
                    }

                    Notification::make()->title("維修單 {$order->order_no} 已建立")->success()->send();

                    return redirect(RepairOrderResource::getUrl('edit', ['record' => $order]));
                }),

            $this->rephotoAction(),

            Action::make('editVehicle')
                ->label('編輯車輛')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->url(fn () => VehicleResource::getUrl('edit', ['record' => $this->record])),
        ];
    }

    /** 重拍 / 補拍車輛照片(header 按鈕與「點圖片」皆會觸發此 action) */
    public function rephotoAction(): Action
    {
        return Action::make('rephoto')
            ->label('重拍車輛圖')
            ->icon('heroicon-o-camera')
            ->color('info')
            ->modalHeading('重拍 / 補拍車輛照片')
            ->modalSubmitActionLabel('儲存照片')
            ->schema([
                PhotoUpload::make('photos', 'vehicle-photos')->hiddenLabel()->required(),
            ])
            ->action(function (array $data) {
                foreach ($data['photos'] ?? [] as $path) {
                    $this->record->photos()->create(['path' => $path, 'user_id' => Auth::id()]);
                }

                Notification::make()->title('已更新車輛照片')->success()->send();
            });
    }
}

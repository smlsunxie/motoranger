<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * 儀表板:車牌搜尋。
 * 輸入部分車牌 → 顯示符合的車輛卡片 → 點卡片進入該車維修檔案頁。
 * 找不到時可一鍵建立車輛並進入該車檔案頁。
 */
class VehicleSearchWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.widgets.vehicle-search-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -3;

    public string $search = '';

    public bool $searched = false;

    public function submitSearch(): void
    {
        $this->searched = true;
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->searched = false;
    }

    /** 找不到車輛時:直接建立車輛並進入檔案頁 */
    public function createVehicleAction(): Action
    {
        return Action::make('createVehicle')
            ->label('建立車輛')
            ->icon('heroicon-o-plus')
            ->modalHeading('建立車輛')
            ->modalSubmitActionLabel('建立並檢視')
            ->schema([
                TextInput::make('plate_no')
                    ->label('車牌號碼')
                    ->required()
                    ->default(fn () => trim($this->search)),
                Select::make('customer_id')
                    ->label('車主')
                    ->helperText('可不填,之後再補登。')
                    ->options(fn () => Customer::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Select::make('brand_id')
                    ->label('廠牌')
                    ->options(fn () => Brand::orderBy('name')->pluck('name', 'id')),
                TextInput::make('model')->label('車型'),
            ])
            ->action(function (array $data) {
                $vehicle = Vehicle::create([
                    'plate_no' => $data['plate_no'],
                    'customer_id' => $data['customer_id'] ?? null,
                    'brand_id' => $data['brand_id'] ?? null,
                    'model' => $data['model'] ?? null,
                ]);

                return redirect(VehicleResource::getUrl('profile', ['record' => $vehicle]));
            });
    }

    /** @return Collection<int, Vehicle> */
    public function getResultsProperty(): Collection
    {
        $term = trim($this->search);

        if ($term === '') {
            return collect();
        }

        return Vehicle::query()
            ->with([
                'brand',
                'customer',
                'photos',
                'latestRepairOrder.user',
                'latestRepairOrder.photos',
            ])
            ->where('plate_no', 'like', "%{$term}%")
            ->orWhereHas('customer', fn (Builder $q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%"))
            ->orderByDesc('updated_at')
            ->limit(48)
            ->get();
    }
}

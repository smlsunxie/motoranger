<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Models\RepairOrder;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRepairOrder extends EditRecord
{
    protected static string $resource = RepairOrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 客戶非必填,由所選車輛自動帶入
        if (empty($data['customer_id']) && ! empty($data['vehicle_id'])) {
            $data['customer_id'] = Vehicle::find($data['vehicle_id'])?->customer_id;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('列印估價單')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (RepairOrder $record) => route('repair-orders.quote', $record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

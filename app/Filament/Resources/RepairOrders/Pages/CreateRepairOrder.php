<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;

class CreateRepairOrder extends CreateRecord
{
    protected static string $resource = RepairOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 客戶非必填,由所選車輛自動帶入
        if (empty($data['customer_id']) && ! empty($data['vehicle_id'])) {
            $data['customer_id'] = Vehicle::find($data['vehicle_id'])?->customer_id;
        }

        return $data;
    }
}

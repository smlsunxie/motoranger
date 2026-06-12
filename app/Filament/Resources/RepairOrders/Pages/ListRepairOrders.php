<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\RepairOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairOrders extends ListRecords
{
    protected static string $resource = RepairOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

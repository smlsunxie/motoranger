<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Models\RepairOrder;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRepairOrder extends ViewRecord
{
    protected static string $resource = RepairOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('列印估價單')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (RepairOrder $record) => route('repair-orders.quote', $record))
                ->openUrlInNewTab(),
            EditAction::make()->label('編輯'),
        ];
    }
}

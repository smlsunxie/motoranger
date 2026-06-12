<?php

namespace App\Filament\Resources\StoreExpenses\Pages;

use App\Filament\Resources\StoreExpenses\StoreExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStoreExpenses extends ListRecords
{
    protected static string $resource = StoreExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

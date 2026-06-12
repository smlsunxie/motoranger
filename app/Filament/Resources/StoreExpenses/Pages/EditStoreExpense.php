<?php

namespace App\Filament\Resources\StoreExpenses\Pages;

use App\Filament\Resources\StoreExpenses\StoreExpenseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStoreExpense extends EditRecord
{
    protected static string $resource = StoreExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

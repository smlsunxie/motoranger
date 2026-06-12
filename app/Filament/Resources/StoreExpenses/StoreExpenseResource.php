<?php

namespace App\Filament\Resources\StoreExpenses;

use App\Filament\Resources\StoreExpenses\Pages\CreateStoreExpense;
use App\Filament\Resources\StoreExpenses\Pages\EditStoreExpense;
use App\Filament\Resources\StoreExpenses\Pages\ListStoreExpenses;
use App\Filament\Resources\StoreExpenses\Schemas\StoreExpenseForm;
use App\Filament\Resources\StoreExpenses\Tables\StoreExpensesTable;
use App\Models\StoreExpense;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreExpenseResource extends Resource
{
    protected static ?string $model = StoreExpense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $modelLabel = '店家支出';

    protected static ?string $pluralModelLabel = '店家支出';

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return StoreExpenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreExpensesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStoreExpenses::route('/'),
            'create' => CreateStoreExpense::route('/create'),
            'edit' => EditStoreExpense::route('/{record}/edit'),
        ];
    }
}

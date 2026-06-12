<?php

namespace App\Filament\Resources\RepairOrders;

use App\Filament\Resources\RepairOrders\Pages\CreateRepairOrder;
use App\Filament\Resources\RepairOrders\Pages\EditRepairOrder;
use App\Filament\Resources\RepairOrders\Pages\ListRepairOrders;
use App\Filament\RelationManagers\NotesRelationManager;
use App\Filament\Resources\RepairOrders\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\RepairOrders\Schemas\RepairOrderForm;
use App\Filament\Resources\RepairOrders\Tables\RepairOrdersTable;
use App\Models\RepairOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RepairOrderResource extends Resource
{
    protected static ?string $model = RepairOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $modelLabel = '維修單';

    protected static ?string $pluralModelLabel = '維修單';

    protected static ?string $recordTitleAttribute = 'order_no';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RepairOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairOrders::route('/'),
            'create' => CreateRepairOrder::route('/create'),
            'edit' => EditRepairOrder::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['order_no', 'customer.name', 'customer.mobile', 'vehicle.plate_no'];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

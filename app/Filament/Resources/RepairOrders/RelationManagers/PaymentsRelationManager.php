<?php

namespace App\Filament\Resources\RepairOrders\RelationManagers;

use App\Enums\PaymentMethod;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = '收款記錄';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('金額')
                    ->numeric()
                    ->required(),
                Select::make('method')
                    ->label('方式')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::Cash)
                    ->required(),
                DatePicker::make('paid_at')
                    ->label('收款日期')
                    ->default(today())
                    ->required(),
                TextInput::make('note')
                    ->label('備注'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paid_at')->label('收款日期')->date('Y-m-d'),
                TextColumn::make('amount')->label('金額')->money('TWD', divideBy: 1),
                TextColumn::make('method')->label('方式'),
                TextColumn::make('note')->label('備注'),
            ])
            ->headerActions([
                CreateAction::make()->label('新增收款'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

<?php

namespace App\Filament\RelationManagers;

use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = '備注';

    protected static ?string $modelLabel = '備注';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('content')
                    ->label('內容')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content')->label('內容')->wrap(),
                TextColumn::make('author_name')->label('作者'),
                TextColumn::make('created_at')->label('時間')->dateTime('Y-m-d H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('新增備注')
                    ->mutateDataUsing(function (array $data): array {
                        $data['author_type'] = User::class;
                        $data['author_id'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

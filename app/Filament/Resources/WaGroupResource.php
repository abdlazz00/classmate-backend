<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaGroupResource\Pages;
use App\Filament\Resources\WaGroupResource\RelationManagers;
use App\Models\WaGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaGroupResource extends Resource
{
    protected static ?string $model = WaGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('class_name')->label('Kelas')->maxLength(30),
                Forms\Components\TextInput::make('group_code')->label('Group Code (Fonnte)')->required()->unique(WaGroup::class, 'group_code', ignoreRecord: true),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('class_name'),
                Tables\Columns\TextColumn::make('group_code'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListWaGroups::route('/'),
            'create' => Pages\CreateWaGroup::route('/create'),
            'edit' => Pages\EditWaGroup::route('/{record}/edit'),
        ];
    }
}

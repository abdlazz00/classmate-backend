<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogReminderResource\Pages;
use App\Filament\Resources\LogReminderResource\RelationManagers;
use App\Models\LogReminder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogReminderResource extends Resource
{
    protected static ?string $model = LogReminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'System';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assignment.name')
                    ->label('Judul Tugas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('group_name')
                    ->label('Target Group'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'sent',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Dikirim pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('sent_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLogReminders::route('/'),
        ];
    }
}

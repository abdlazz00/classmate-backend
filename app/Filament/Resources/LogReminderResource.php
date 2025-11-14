<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogReminderResource\Pages;
use App\Models\LogReminder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class LogReminderResource extends Resource
{
    protected static ?string $model = LogReminder::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Logging';
    protected static ?string $navigationLabel = 'Daily Reminder Logs';

    public static function canCreate(): bool
    {
        return false; // Log otomatis, tidak boleh create manual
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Waktu')
                    ->dateTime('d M H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignment.title')
                    ->label('Tugas')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('group_name')
                    ->label('Target Group')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'success',
                        'danger' => 'failed',
                    ]),

                // Kolom Intip Error
                Tables\Columns\TextColumn::make('note')
                    ->limit(20)
                    ->tooltip(fn ($state) => $state)
                    ->label('Note')
                    ->color('gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-exclamation-circle' : null),
            ])
            ->defaultSort('sent_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Tampilan Detail (Mata)
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Status Pengiriman')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('sent_at')->dateTime(),
                        TextEntry::make('status')->badge()->colors(['success'=>'success', 'danger'=>'failed']),
                        TextEntry::make('group_name')->label('Grup WA'),
                    ]),

                Section::make('Konten Pesan')
                    ->schema([
                        TextEntry::make('message')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                Section::make('Error Log')
                    ->schema([
                        TextEntry::make('note')
                            ->fontFamily('mono')
                            ->columnSpanFull()
                            ->default('Tidak ada error.'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->status === 'success'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLogReminders::route('/'),
        ];
    }
}

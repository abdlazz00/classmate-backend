<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BroadcastLogResource\Pages;
use App\Filament\Resources\BroadcastLogResource\RelationManagers;
use App\Models\BroadcastLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class BroadcastLogResource extends Resource
{
    protected static ?string $model = BroadcastLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Broadcast')
                ->schema([
                    TextEntry::make('Title')
                        ->label('Judul')
                        ->icon('heroicon-m-document-text'),

                    TextEntry::make('target_group')
                        ->label('Target Group')
                        ->icon('heroicon-m-user-group'),

                    TextEntry::make('type')
                        ->label('Tipe')
                        ->badge()
                        ->colors([
                            'info' => 'announcement',
                            'success' => 'material',
                            'warning' => 'assignment',
                            'primary' => 'reminder',
                        ]),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'success' => 'success',
                            'failed' => 'danger',
                            default => 'gray',
                        }),

                    TextEntry::make('user.name')
                        ->label('Pengirim')
                        ->placeholder('Sistem (Otomatis)'),

                    TextEntry::make('created_at')
                        ->label('Waktu Kirim')
                        ->dateTime('d M Y, H:i'),

                    TextEntry::make('message')
                        ->label('Isi Pesan WhatsApp')
                        ->markdown() // Agar format pesan WA terbaca rapi
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'bg-gray-50 p-4 rounded-lg']),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('create_at')
                    ->label('Waktu Kirim')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Broadcast')
                    ->badge()
                    ->colors([
                        'info' => 'announcement',
                        'success' => 'material',
                        'warning' => 'assignment',
                        'primary' => 'reminder',
                    ]),

                Tables\Columns\TextColumn::make('target_group')
                    ->label('Group WA')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengirim')
                    ->placeholder('Sistem'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
                SelectFilter::make('type')
                    ->options([
                        'assignment' => 'Tugas',
                        'material' => 'Materi',
                        'announcement' => 'Pengumuman',
                        'reminder' => 'Reminder Harian',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Pesan'),
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
            'index' => Pages\ManageBroadcastLogs::route('/'),
        ];
    }
}

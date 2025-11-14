<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BroadcastLogResource\Pages;
use App\Models\BroadcastLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Tables\Filters\SelectFilter;

class BroadcastLogResource extends Resource
{
    protected static ?string $model = BroadcastLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Logging';
    protected static ?string $navigationLabel = 'Broadcast Logs';

    // Matikan fitur create manual karena log dibuat oleh sistem
    public static function canCreate(): bool
    {
        return false;
    }

    // Form tidak digunakan, jadi biarkan kosong
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->label('Waktu'),

                Tables\Columns\TextColumn::make('target_group')
                    ->searchable()
                    ->label('Target'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'info' => 'announcement',
                        'success' => 'material',
                        'warning' => 'assignment',
                        'primary' => 'reminder',
                    ]),

                // Status dengan warna Merah/Hijau
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'success',
                        'danger' => 'failed',
                    ]),

                // Intip error log di tabel (dipotong biar ga panjang)
                Tables\Columns\TextColumn::make('note')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state) // Hover untuk lihat full
                    ->label('Log Note')
                    ->color('gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-exclamation-circle' : null),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'assignment' => 'Tugas',
                        'material' => 'Materi',
                        'announcement' => 'Pengumuman',
                        'reminder' => 'Reminder',
                    ]),
            ])
            ->actions([
                // ViewAction ini yang akan memanggil Infolist
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Ini pengganti tampilan Form untuk melihat detail
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // BAGIAN 1: Informasi Status
                Section::make('Status Pengiriman')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime('d F Y, H:i:s')
                            ->label('Waktu Eksekusi'),

                        TextEntry::make('status')
                            ->badge()
                            ->colors([
                                'success' => 'success',
                                'danger' => 'failed',
                            ]),

                        TextEntry::make('user.name')
                            ->label('Dipicu Oleh')
                            ->placeholder('Sistem Otomatis'),
                    ]),

                // BAGIAN 2: Detail Konten
                Section::make('Detail Pesan')
                    ->schema([
                        TextEntry::make('type')
                            ->label('Tipe Broadcast')
                            ->badge(),

                        TextEntry::make('target_group')
                            ->label('Grup Tujuan')
                            ->icon('heroicon-o-user-group'),

                        TextEntry::make('title')
                            ->label('Judul')
                            ->weight('bold'),

                        TextEntry::make('message')
                            ->label('Isi Pesan')
                            ->columnSpanFull()
                            ->markdown() // Agar enter/bold di pesan WA terlihat rapi
                            ->prose(),
                    ]),

                // BAGIAN 3: Log Error (Penting untuk Debugging)
                Section::make('Technical Log & Error')
                    ->schema([
                        TextEntry::make('note')
                            ->label('Server Response / Error Message')
                            ->fontFamily('mono') // Font coding
                            ->state(function ($record) {
                                // Coba format JSON biar rapi kalau isinya JSON
                                $json = json_decode($record->note);
                                return $json ? json_encode($json, JSON_PRETTY_PRINT) : $record->note;
                            })
                            ->columnSpanFull()
                            ->default('Tidak ada catatan error.'),
                    ])
                    ->collapsible() // Bisa ditutup/buka
                    ->collapsed(fn ($record) => $record->status === 'success'), // Auto tutup kalau sukses
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBroadcastLogs::route('/'),
        ];
    }
}

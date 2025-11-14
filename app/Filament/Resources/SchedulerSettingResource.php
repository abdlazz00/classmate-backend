<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchedulerSettingResource\Pages;
use App\Models\SchedulerSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class SchedulerSettingResource extends Resource
{
    protected static ?string $model = SchedulerSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Pengaturan Jadwal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konfigurasi Jadwal')
                    ->description('Atur kapan sistem akan mengirim reminder otomatis.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Tugas')
                            ->readOnly()
                            ->columnSpanFull(),

                        // PILIH MODE
                        Forms\Components\Select::make('mode')
                            ->label('Tipe Jadwal')
                            ->options([
                                'specific' => 'Jam Tertentu (Waktu Spesifik)',
                                'interval' => 'Interval (Setiap X Waktu)',
                            ])
                            ->default('specific')
                            ->live() // Agar form di bawahnya responsif
                            ->afterStateUpdated(fn (Set $set) => $set('unit', null))
                            ->required(),

                        // OPSI A: JIKA MEMILIH JAM TERTENTU
                        Forms\Components\Repeater::make('time_details')
                            ->label('Daftar Waktu Eksekusi')
                            ->schema([
                                Forms\Components\TimePicker::make('time')
                                    ->label('Pukul')
                                    ->seconds(false) // Format HH:MM
                                    ->required(),
                            ])
                            ->visible(fn (Get $get) => $get('mode') === 'specific')
                            ->addActionLabel('Tambah Jam')
                            ->grid(3) // Tampil 3 kolom agar rapi
                            ->columnSpanFull(),

                        // OPSI B: JIKA MEMILIH INTERVAL
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->label('Setiap...')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Forms\Components\Select::make('unit')
                                    ->label('Satuan')
                                    ->options([
                                        'minutes' => 'Menit',
                                        'hours'   => 'Jam',
                                        'days'    => 'Hari',
                                    ])
                                    ->required(),
                            ])
                            ->visible(fn (Get $get) => $get('mode') === 'interval'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Jadwal Ini')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Job'),

                Tables\Columns\TextColumn::make('mode')
                    ->badge()
                    ->colors([
                        'info' => 'interval',
                        'success' => 'specific',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'interval' => 'Interval',
                        'specific' => 'Jam Tertentu',
                    }),

                Tables\Columns\TextColumn::make('summary')
                    ->label('Jadwal')
                    ->getStateUsing(function (SchedulerSetting $record) {
                        if ($record->mode === 'interval') {
                            return "Setiap {$record->value} " . ucfirst($record->unit);
                        }
                        // Jika specific, tampilkan jam-jamnya
                        $times = collect($record->time_details)->pluck('time')->join(', ');
                        return $times ?: '-';
                    }),

                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedulerSettings::route('/'),
            'edit' => Pages\EditSchedulerSetting::route('/{record}/edit'),
        ];
    }
}

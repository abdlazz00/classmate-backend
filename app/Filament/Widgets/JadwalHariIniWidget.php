<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class JadwalHariIniWidget extends BaseWidget
{
    protected static ?string $heading = 'Jadwal Hari Ini';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        // Mendapatkan nama hari ini dalam bahasa Inggris (sesuai standar Carbon)
        $namaHariIni = now()->format('l');

        // Catatan: Pastikan data 'day' di database Schedule Anda
        // tersimpan dalam format 'Monday', 'Tuesday', dst.
        // Jika tidak (misal: 'Senin'), Anda perlu menyesuaikan logikanya.

        return $table
            ->query(
                Schedule::query()
                    ->with('course') // Asumsi relasi 'Course' ada di model Schedule
                    ->where('day', $namaHariIni)
                    ->orderBy('start_time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('course.name')->label('Mata Kuliah'),
                Tables\Columns\TextColumn::make('course.lecturer')->label('Dosen'),
                Tables\Columns\TextColumn::make('room')->label('Ruangan'),
                Tables\Columns\TextColumn::make('start_time')->label('Mulai')->time('H:i'),
                Tables\Columns\TextColumn::make('end_time')->label('Selesai')->time('H:i'),
            ])
            ->paginated(false);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TugasAktifWidget extends BaseWidget
{
    protected static ?string $heading = 'Tugas Aktif (Belum Deadline)';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Assignment::query()
                    ->with('course') // Relasi dari Assignment->course()
                    ->where('deadline', '>=', now())
                    ->orderBy('deadline', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('course.name')->label('Mata Kuliah'),
                Tables\Columns\TextColumn::make('title')->label('Judul Tugas'),
                Tables\Columns\TextColumn::make('deadline')->label('Deadline')->dateTime()->sortable(),
            ])
            ->paginated(false);
    }
}

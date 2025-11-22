<?php

namespace App\Filament\Widgets;

use App\Models\BroadcastLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LogBroadcastTerbaruWidget extends BaseWidget
{
    protected static ?string $heading = 'Log Broadcast WA Terbaru';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BroadcastLog::query()->latest()->limit(10) // Ambil 10 log terbaru
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime(),
                Tables\Columns\TextColumn::make('type')->label('Tipe'),
                Tables\Columns\TextColumn::make('target')->label('Target'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('response')->label('Respon Fonnte')->limit(50),
            ])
            ->paginated(false);
    }
}

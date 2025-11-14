<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogDownloadResource\Pages;
use App\Filament\Resources\LogDownloadResource\RelationManagers;
use App\Models\LogDownload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogDownloadResource extends Resource
{
    protected static ?string $model = LogDownload::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Logging';
    protected static ?string $navigationLabel = 'Download Logs';


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
                Tables\Columns\TextColumn::make("user.name")
                    ->label('Mahasiswa')
                    ->searchable(),

                Tables\Columns\TextColumn::make("material.name")
                    ->label('Materi')
                    ->searchable(),

                Tables\Columns\TextColumn::make("downloaded_at")
                    ->label('Tanggal Download')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('downloaded_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ManageLogDownloads::route('/'),
        ];
    }
}

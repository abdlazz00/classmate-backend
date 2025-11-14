<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    Protected static ?string $navigationGroup = 'Akademik';
    Protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->relationship('course','name')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('day')
                    ->options([
                    'senin'=>'Senin','selasa'=>'Selasa','rabu'=>'Rabu',
                    'kamis'=>'Kamis','jumat'=>'Jumat','sabtu'=>'Sabtu'
                    ])
                    ->label('Hari')
                    ->required(),
                Forms\Components\TimePicker::make('start_time')->required(),
                Forms\Components\TimePicker::make('end_time')->required(),
                Forms\Components\TextInput::make('room')
                    ->label('Ruangan')
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')->label('Mata Kuliah')->searchable(),
                Tables\Columns\TextColumn::make('day')->label('Hari')->sortable(),
                Tables\Columns\TextColumn::make('start_time')->label('Mulai'),
                Tables\Columns\TextColumn::make('end_time')->label('Selesai'),
                Tables\Columns\TextColumn::make('room')->label('Ruangan'),
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}

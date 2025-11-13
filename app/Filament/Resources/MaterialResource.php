<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->relationship('course','name')
                    ->required()
                    ->searchable()
                    ->label('Mata Kuliah'),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Judul Materi'),

                Forms\Components\Textarea::make('description')
                    ->rows(4)
                    ->label('Deskripsi Materi'),

                Forms\Components\Select::make('type')
                    ->options([
                        'pdf' => 'PDF',
                        'doc' => 'Word',
                        'xlx' => 'Excel',
                        'ppt' => 'PPT',
                        'link'=> 'Tautan',
                    ])
                    ->required()
                    ->default('pdf')
                    ->label('Tipe File'),

                Forms\Components\FileUpload::make('file_path')
                    ->directory('materials')
                    ->required()
                    ->preserveFilenames()
                    ->label('Upload File'),

                Forms\Components\Hidden::make('uploader_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Mata Kuliah')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe File')
                    ->badge()
                    ->colors([
                        'primary',
                        'success' => 'pdf',
                        'warning' => 'doc',
                        'danger' => 'xlx',
                        'info' => 'ppt',
                        'light' => 'link',
                    ]),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploader')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diupload')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime(),
                ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                ->options([
                    'pdf' => 'PDF',
                    'doc' => 'Word',
                    'xlx' => 'Excel',
                    'ppt' => 'PPT',
                    'link'=> 'Tautan',
                ])
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Material $record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn(array $data) => $data + ['uploader_id' => Auth::id()]),
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}

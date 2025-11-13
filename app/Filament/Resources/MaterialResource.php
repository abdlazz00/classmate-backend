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
                Forms\Components\Select::make('course_id')->relationship('course','name')->required()->searchable(),
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\Textarea::make('description')->rows(3),
                Forms\Components\FileUpload::make('files')
                    ->label('Upload Files')
                    ->multiple()
                    ->maxSize(10240) // 10MB per file
                    ->directory('materials')
                    ->disk('public')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    ])
                    ->preserveFilenames()
                    ->saveUploadedFileUsing(function ($file, Material $record) {
                        // gunakan Spatie Media Library untuk menyimpan file
                        $record->addMedia($file->getRealPath())
                            ->usingFileName($file->getClientOriginalName())
                            ->toMediaCollection('files', 'public');
                        // return null agar Filament tidak menyimpan path ke DB (Material tidak punya kolom file path)
                        return null;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('course.name')->label('Mata Kuliah'),
                Tables\Columns\TextColumn::make('files_count')
                    ->label('Files')
                    ->getStateUsing(fn($record) => $record->getMedia('files')->count()),
                Tables\Columns\TextColumn::make('created_at')->label('Diupload')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('list_files')
                    ->label('Files')
                    ->modalHeading('File Materi')
                    ->modalContent(fn($record) => view('filament.material-files', [
                        'files' => $record->getMedia('files')
                    ])),
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

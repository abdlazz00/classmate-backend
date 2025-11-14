<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Filament\Resources\AssignmentResource\RelationManagers;
use App\Jobs\BroadcastAssignmentToWa;
use App\Models\Assignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Models\WaGroup;
use Filament\Forms\Components\CheckboxList;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Tugas';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')->relationship('course','name')->required()->searchable(),
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\RichEditor::make('description')->toolbarButtons(['bold','italic','bulletList','link'])->nullable(),
                Forms\Components\DateTimePicker::make('deadline')->required(),
                Forms\Components\Select::make('status')->options(['open'=>'Open','closed'=>'Closed'])->default('open'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('course.name')->label('Mata Kuliah'),
                Tables\Columns\TextColumn::make('deadline')->dateTime()->sortable(),
                Tables\Columns\BadgeColumn::make('status')->colors(['success'=>'open','danger'=>'closed']),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn(array $data)=> $data + ['created_by' => Auth::id()]),

                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('broadcast')
                    ->label('Kirim Ke WA')
                    ->action(fn($record) => BroadcastAssignmentToWa::dispatch($record)),

                Tables\Actions\Action::make('broadcast')
                    ->label('Broadcast WA')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        CheckboxList::make('wa_group_ids')
                            ->label('Pilih Target Grup')
                            ->options(WaGroup::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->columns(2)
                            ->default(fn ($record) => WaGroup::where('class_name', $record->course->class_name)->pluck('id')->toArray()),
                    ])
                    ->modalHeading('Kirim Tugas ke WhatsApp')
                    ->modalSubmitActionLabel('Kirim')
                    ->action(function ($record, array $data) {
                        BroadcastAssignmentToWa::dispatch(
                            $record,
                            $data['wa_group_ids'],
                            auth()->id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Tugas sedang dikirim ke WA')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}

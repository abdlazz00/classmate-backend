<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers;
use App\Jobs\BroadcastAnnouncementToWa;
use App\Models\Announcement;
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

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Kominukasi';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\Textarea::make('message')->required()->rows(4),
                Forms\Components\TextInput::make('class_name')->label('Kelas (opsional)')->placeholder('Misal: 2A'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('class_name')->label('Kelas'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn(array $data) => $data + ['created_by' => Auth::id()]),

                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('broadcast')
                    ->label('Kirim Ke WA')
                    ->action(fn($record)=>BroadcastAnnouncementToWa::dispatch($record)),

                Tables\Actions\Action::make('broadcast')
                    ->label('Kirim Ke WA')
                    ->icon('heroicon-o-megaphone')
                    ->color('success')
                    ->form([
                        CheckboxList::make('wa_group_ids')
                            ->label('Pilih Target Grup (Opsional)')
                            ->helperText('Jika dikosongkan, sistem akan mengirim sesuai "Kelas" di data pengumuman. Jika Kelas kosong, akan dikirim ke SEMUA grup.')
                            ->options(WaGroup::where('is_active', true)->pluck('name', 'id'))
                            ->columns(2)
                            ->searchable(),
                    ])
                    ->modalHeading('Broadcast Pengumuman')
                    ->modalSubmitActionLabel('Kirim Pesan')
                    ->action(function ($record, array $data) {
                        // DISPATCH JOB DENGAN USER ID
                        BroadcastAnnouncementToWa::dispatch(
                            $record,
                            $data['wa_group_ids'] ?? [],
                            auth()->id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Pengumuman sedang dikirim...')
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}

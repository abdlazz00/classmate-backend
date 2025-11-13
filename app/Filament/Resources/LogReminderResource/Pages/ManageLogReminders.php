<?php

namespace App\Filament\Resources\LogReminderResource\Pages;

use App\Filament\Resources\LogReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLogReminders extends ManageRecords
{
    protected static string $resource = LogReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

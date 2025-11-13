<?php

namespace App\Filament\Resources\BroadcastLogResource\Pages;

use App\Filament\Resources\BroadcastLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBroadcastLogs extends ManageRecords
{
    protected static string $resource = BroadcastLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

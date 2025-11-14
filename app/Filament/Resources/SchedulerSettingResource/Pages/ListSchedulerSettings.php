<?php

namespace App\Filament\Resources\SchedulerSettingResource\Pages;

use App\Filament\Resources\SchedulerSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchedulerSettings extends ListRecords
{
    protected static string $resource = SchedulerSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

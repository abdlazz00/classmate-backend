<?php

namespace App\Filament\Resources\SchedulerSettingResource\Pages;

use App\Filament\Resources\SchedulerSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchedulerSetting extends EditRecord
{
    protected static string $resource = SchedulerSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

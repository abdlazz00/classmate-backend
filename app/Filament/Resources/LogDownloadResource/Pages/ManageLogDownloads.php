<?php

namespace App\Filament\Resources\LogDownloadResource\Pages;

use App\Filament\Resources\LogDownloadResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLogDownloads extends ManageRecords
{
    protected static string $resource = LogDownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Clusters\Region\Resources\VillageResource\Pages;

use App\Filament\Clusters\Region\Resources\VillageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVillage extends EditRecord
{
    protected static string $resource = VillageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

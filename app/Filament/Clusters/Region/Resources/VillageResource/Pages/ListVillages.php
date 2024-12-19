<?php

namespace App\Filament\Clusters\Region\Resources\VillageResource\Pages;

use App\Filament\Clusters\Region\Resources\VillageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVillages extends ListRecords
{
    protected static string $resource = VillageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

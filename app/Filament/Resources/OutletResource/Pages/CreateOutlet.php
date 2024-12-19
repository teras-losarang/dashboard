<?php

namespace App\Filament\Resources\OutletResource\Pages;

use App\Filament\Resources\OutletResource;
use App\Models\OutletImage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOutlet extends CreateRecord
{
    protected static string $resource = OutletResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['images'] = json_encode($data['images']);
        $data['operational_hour'] = json_encode($data['operational_hour']);

        return $data;
    }

    // protected function afterCreate(): void
    // {
    //     DB::transaction(function () {
    //         $record = $this->record;

    //         $images = $this->form->getState()["images"];

    //         foreach ($images as $image) {
    //             OutletImage::create([
    //                 "outlet_id" => $record->id,
    //                 "image" => $image
    //             ]);
    //         }
    //     });
    // }
}

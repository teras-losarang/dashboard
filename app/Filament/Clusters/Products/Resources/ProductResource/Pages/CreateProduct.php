<?php

namespace App\Filament\Clusters\Products\Resources\ProductResource\Pages;

use App\Filament\Clusters\Products\Resources\ProductResource;
use App\Models\ProductVariant;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['images'] = json_encode($data['images']);

        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $record = $this->record;

            if ($this->form->getState()["enable_variant"]) {
                $variants = $this->form->getState()["variants"];

                foreach ($variants as $variant) {
                    ProductVariant::create([
                        "product_id" => $record->id,
                        "name" => $variant["name"],
                        "price" => $variant["price"],
                        "status" => $variant["status"],
                    ]);
                }
            }
        });
    }
}

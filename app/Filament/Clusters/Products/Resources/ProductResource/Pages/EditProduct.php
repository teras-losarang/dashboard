<?php

namespace App\Filament\Clusters\Products\Resources\ProductResource\Pages;

use App\Filament\Clusters\Products\Resources\ProductResource;
use App\Models\ProductVariant;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data["images"] = json_decode($data['images'], true);
        $data["variants"] = $this->record->variants->map(function ($variant) {
            return [
                "name" => $variant->name,
                "price" => $variant->price,
                "status" => $variant->status,
            ];
        })->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data["images"] = json_encode($data['images'], true);

        return $data;
    }

    protected function afterSave(): void
    {
        DB::transaction(function () {
            $record = $this->record;
            $record->variants()->delete();

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

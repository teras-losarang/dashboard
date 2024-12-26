<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\StatusTypeEnum;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\StatsOverview;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        $order = Order::query();

        $orderIn = clone $order;
        $orderDone = clone $order;
        $orderCancel = clone $order;

        return [
            "all" => Tab::make("All Order"),
            "pendingOrder" => Tab::make("Order Pending")->modifyQueryUsing(function ($query) {
                return $query->where('status', StatusTypeEnum::ORDER);
            })->badge($orderIn->where('status', StatusTypeEnum::ORDER)->count()),
            "doneOrder" => Tab::make("Order Done")->modifyQueryUsing(function ($query) {
                return $query->where('status', StatusTypeEnum::DONE);
            })->badge($orderDone->where('status', StatusTypeEnum::DONE)->count()),
            "cancelOrder" => Tab::make("Order Cancel")->modifyQueryUsing(function ($query) {
                return $query->where('status', StatusTypeEnum::CANCEL);
            })->badge($orderCancel->where('status', StatusTypeEnum::CANCEL)->count()),
        ];
    }
}

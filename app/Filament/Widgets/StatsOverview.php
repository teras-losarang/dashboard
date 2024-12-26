<?php

namespace App\Filament\Widgets;

use App\Enums\StatusTypeEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $maxHeight = '100px';

    protected function getStats(): array
    {
        $order = Order::query();

        $orderIn = clone $order;
        $orderDone = clone $order;
        $orderCancel = clone $order;

        return [
            Stat::make('Order In', $orderIn->where('status', StatusTypeEnum::ORDER)->count()),
            Stat::make('Order Done', $orderDone->where('status', StatusTypeEnum::DONE)->count()),
            Stat::make('Order Cancel', $orderCancel->where('status', StatusTypeEnum::CANCEL)->count()),
        ];
    }
}

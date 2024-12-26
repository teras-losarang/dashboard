<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Enums\StatusTypeEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $order = Order::query();
        $orderDone = clone $order;

        return [
            Stat::make('Total Order', $order->count()),
            Stat::make('Order Done', $orderDone->where('status', StatusTypeEnum::DONE)->count()),
            Stat::make('Profit', str_replace(",00", "", Number::currency($order->sum('total'), 'IDR', 'id'))),
        ];
    }
}

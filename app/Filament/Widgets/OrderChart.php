<?php

namespace App\Filament\Widgets;

use App\Enums\StatusTypeEnum;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrderChart extends ChartWidget
{
    protected static ?string $heading = 'Order';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $year = 2024;

        $orderCounts = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('status'),
            DB::raw('COUNT(*) as count')
        )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'), 'status')
            ->get();

        $orderInData = array_fill(1, 12, 0);
        $orderDoneData = array_fill(1, 12, 0);
        $orderCancelData = array_fill(1, 12, 0);

        foreach ($orderCounts as $orderCount) {
            switch ($orderCount->status) {
                case StatusTypeEnum::ORDER:
                    $orderInData[$orderCount->month] = $orderCount->count;
                    break;
                case StatusTypeEnum::DONE:
                    $orderDoneData[$orderCount->month] = $orderCount->count;
                    break;
                case StatusTypeEnum::CANCEL:
                    $orderCancelData[$orderCount->month] = $orderCount->count;
                    break;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Order In',
                    'data' => array_values($orderInData),
                ],
                [
                    'label' => 'Order Done',
                    'data' => array_values($orderDoneData),
                    'backgroundColor' => '#4ade804d',
                    'borderColor' => '#4ADE80',
                ],
                [
                    'label' => 'Order Cancel',
                    'data' => array_values($orderCancelData),
                    'backgroundColor' => '#f871714d',
                    'borderColor' => '#F87171',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}

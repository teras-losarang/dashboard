<?php

namespace App\Filament\Widgets;

use App\Enums\StatusTypeEnum;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::orderBy("id", "desc")->limit(5))
            ->columns([
                TextColumn::make("name")->description(function ($record) {
                    return "Account: {$record->user->name}";
                }),
                TextColumn::make("phone"),
                TextColumn::make("total")->money("IDR", 0, "id"),
                TextColumn::make("status")->badge()->formatStateUsing(function ($state) {
                    return StatusTypeEnum::show($state);
                })->color(function ($state) {
                    return StatusTypeEnum::color($state);
                })
            ])->paginated(false);
    }
}

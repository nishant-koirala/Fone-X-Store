<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\TradeInValuation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', 'Rs '.number_format(Order::where('status', 'fulfilled')->sum('total'), 2))
                ->description('From fulfilled orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->description('Awaiting fulfillment')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),

            Stat::make('Pending Trade-Ins', TradeInValuation::where('status', 'pending')->count())
                ->description('Awaiting inspection')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('info'),
        ];
    }
}

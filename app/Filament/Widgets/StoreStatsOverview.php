<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '$' . number_format(\App\Models\Order::where('status', 'fulfilled')->sum('total'), 2))
                ->description('From fulfilled orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Pending Orders', \App\Models\Order::where('status', 'pending')->count())
                ->description('Awaiting fulfillment')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
                
            Stat::make('Pending Trade-Ins', \App\Models\TradeInValuation::where('status', 'pending')->count())
                ->description('Awaiting inspection')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('info'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use App\Models\BillItem;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpenseStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $gross = BillItem::totalFor(BillItem::query());
        $returned = BillItem::totalFor(BillItem::query()->returned());
        $waived = BillItem::totalFor(BillItem::query()->waived());
        $chargeable = BillItem::totalFor(BillItem::query()->active());
        $guyRefunds = BillItem::totalFor(
            BillItem::query()
                ->where('status', BillItemStatus::Returned)
                ->where('refunded_by', RefundedBy::RestorationGuy),
        );
        $activeProjects = Project::query()->where('is_active', true)->count();

        return [
            Stat::make('Charge restoration guy', '₹'.number_format($chargeable, 2))
                ->description('Items still claimed from the guy')
                ->color('success'),
            Stat::make('Gross purchases', '₹'.number_format($gross, 2))
                ->description("{$activeProjects} active project(s)")
                ->color('primary'),
            Stat::make('Returned', '₹'.number_format($returned, 2))
                ->description('₹'.number_format($guyRefunds, 2).' paid back by restoration guy')
                ->color('warning'),
            Stat::make('I pay', '₹'.number_format($waived, 2))
                ->description('Not claimed from the restoration guy')
                ->color('gray'),
        ];
    }
}

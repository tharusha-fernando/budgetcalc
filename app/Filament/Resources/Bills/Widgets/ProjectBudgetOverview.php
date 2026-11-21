<?php

namespace App\Filament\Resources\Bills\Widgets;

use App\Models\Bill;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class ProjectBudgetOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    #[Locked]
    public ?Model $record = null;

    protected function getHeading(): ?string
    {
        $project = $this->project();

        if (! $project) {
            return 'Project budget';
        }

        return 'Project budget · '.$project->name;
    }

    protected function getDescription(): ?string
    {
        $project = $this->project();

        if (! $project) {
            return null;
        }

        $parts = array_filter([
            $project->vehicle,
            $project->restoration_guy ? 'Guy: '.$project->restoration_guy : null,
        ]);

        return $parts === [] ? null : implode(' · ', $parts);
    }

    protected function getStats(): array
    {
        $project = $this->project();

        if (! $project) {
            return [];
        }

        $agreed = $project->agreed_amount !== null ? (float) $project->agreed_amount : null;
        $chargeable = $project->chargeableTotal();
        $gross = $project->grossTotal();
        $returned = $project->returnedTotal();
        $waived = $project->waivedTotal();
        $difference = $agreed === null ? null : $chargeable - $agreed;

        return [
            Stat::make(
                'Agreed budget',
                $agreed === null ? '—' : '₹'.number_format($agreed, 2),
            )
                ->description('With restoration guy')
                ->color('primary'),
            Stat::make('Gross spent', '₹'.number_format($gross, 2))
                ->description('All project purchases')
                ->color('gray'),
            Stat::make('Charge guy', '₹'.number_format($chargeable, 2))
                ->description(
                    $difference === null
                        ? 'Still claimed'
                        : 'Diff ₹'.number_format($difference, 2),
                )
                ->color($difference !== null && $difference > 0 ? 'danger' : 'success'),
            Stat::make('Returned / I pay', '₹'.number_format($returned + $waived, 2))
                ->description(
                    'Returned ₹'.number_format($returned, 2)
                    .' · I pay ₹'.number_format($waived, 2),
                )
                ->color('warning'),
        ];
    }

    protected function project(): ?Project
    {
        if (! $this->record instanceof Bill) {
            return null;
        }

        $this->record->loadMissing('project');

        return $this->record->project;
    }
}

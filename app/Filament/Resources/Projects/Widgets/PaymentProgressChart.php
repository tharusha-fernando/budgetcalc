<?php

namespace App\Filament\Resources\Projects\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class PaymentProgressChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    #[Locked]
    public ?Model $record = null;

    public function getHeading(): string|Htmlable|null
    {
        return 'Payment progress';
    }

    public function getDescription(): string|Htmlable|null
    {
        $project = $this->project();

        if (! $project) {
            return null;
        }

        $paid = $project->paidToRestorationGuyTotal();
        $agreed = $project->agreed_amount !== null ? (float) $project->agreed_amount : null;

        if ($agreed === null) {
            return 'Paid ₹'.number_format($paid, 2).' · set an agreed amount to track remaining';
        }

        $remaining = $project->stillOwedToRestorationGuy() ?? 0.0;
        $percent = $agreed > 0 ? min(100, round(($paid / $agreed) * 100)) : 0;

        return 'Paid ₹'.number_format($paid, 2)
            .' of ₹'.number_format($agreed, 2)
            .' ('.$percent.'%)'
            .' · Still ₹'.number_format($remaining, 2);
    }

    protected function getData(): array
    {
        $project = $this->project();

        if (! $project) {
            return [
                'datasets' => [
                    [
                        'data' => [0],
                        'backgroundColor' => ['#d1d5db'],
                    ],
                ],
                'labels' => ['No project'],
            ];
        }

        $paid = round($project->paidToRestorationGuyTotal(), 2);
        $agreed = $project->agreed_amount !== null ? (float) $project->agreed_amount : null;

        if ($agreed === null) {
            return [
                'datasets' => [
                    [
                        'data' => [$paid > 0 ? $paid : 1],
                        'backgroundColor' => [$paid > 0 ? '#0ea5e9' : '#e5e7eb'],
                    ],
                ],
                'labels' => [$paid > 0 ? 'Paid to guy' : 'No payments yet'],
            ];
        }

        $remaining = round(max(0.0, $agreed - $paid), 2);
        $overpaid = round(max(0.0, $paid - $agreed), 2);
        $paidTowardAgreed = round(min($paid, $agreed), 2);

        if ($overpaid > 0) {
            return [
                'datasets' => [
                    [
                        'data' => [$paidTowardAgreed, $overpaid],
                        'backgroundColor' => ['#10b981', '#8b5cf6'],
                    ],
                ],
                'labels' => ['Paid (agreed)', 'Overpaid'],
            ];
        }

        if ($paidTowardAgreed <= 0 && $remaining <= 0) {
            return [
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['#e5e7eb'],
                    ],
                ],
                'labels' => ['No payments yet'],
            ];
        }

        return [
            'datasets' => [
                [
                    'data' => [
                        $paidTowardAgreed > 0 ? $paidTowardAgreed : 0,
                        $remaining > 0 ? $remaining : 0,
                    ],
                    'backgroundColor' => ['#10b981', '#f59e0b'],
                ],
            ],
            'labels' => ['Paid to guy', 'Still to pay'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '55%',
        ];
    }

    protected function project(): ?Project
    {
        return $this->record instanceof Project ? $this->record : null;
    }
}

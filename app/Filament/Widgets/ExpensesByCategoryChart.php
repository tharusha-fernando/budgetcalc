<?php

namespace App\Filament\Widgets;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Models\BillItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ExpensesByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Chargeable spend by category';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $rows = BillItem::query()
            ->where('status', BillItemStatus::Active)
            ->select('category', DB::raw('SUM(quantity * unit_price) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $labels = [];
        $data = [];

        foreach (BillItemCategory::cases() as $category) {
            $labels[] = $category->getLabel();
            $data[] = round((float) ($rows[$category->value] ?? 0), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Chargeable (₹)',
                    'data' => $data,
                    'backgroundColor' => [
                        '#f59e0b',
                        '#3b82f6',
                        '#10b981',
                        '#8b5cf6',
                        '#6b7280',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

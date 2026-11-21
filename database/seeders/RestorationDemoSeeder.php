<?php

namespace Database\Seeders;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Project;
use Illuminate\Database\Seeder;

class RestorationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::query()->create([
            'name' => 'Classic Jeep Restoration',
            'vehicle' => '1985 Jeep CJ-7',
            'restoration_guy' => 'Ravi Workshop',
            'agreed_amount' => 175000,
            'notes' => 'Demo project for expense tracking.',
            'is_active' => true,
        ]);

        $paintBill = Bill::query()->create([
            'project_id' => $project->id,
            'vendor' => 'Auto Paint Mart',
            'bill_number' => 'APM-1024',
            'bill_date' => now()->subDays(20)->toDateString(),
            'stated_total' => 8400,
            'notes' => 'Primer and paint materials',
        ]);

        BillItem::query()->create([
            'bill_id' => $paintBill->id,
            'description' => 'Priming milk',
            'category' => BillItemCategory::Materials,
            'quantity' => 2,
            'unit_price' => 1200,
            'status' => BillItemStatus::Returned,
            'refunded_by' => RefundedBy::RestorationGuy,
            'settled_at' => now()->subDays(10)->toDateString(),
            'notes' => 'Restoration guy paid this back.',
        ]);

        BillItem::query()->create([
            'bill_id' => $paintBill->id,
            'description' => 'Body filler',
            'category' => BillItemCategory::Materials,
            'quantity' => 1,
            'unit_price' => 2500,
            'status' => BillItemStatus::Active,
        ]);

        BillItem::query()->create([
            'bill_id' => $paintBill->id,
            'description' => 'Sandpaper pack',
            'category' => BillItemCategory::Tools,
            'quantity' => 3,
            'unit_price' => 350,
            'status' => BillItemStatus::Waived,
            'settled_at' => now()->subDays(5)->toDateString(),
            'notes' => 'Absorbed by owner.',
        ]);

        $partsBill = Bill::query()->create([
            'project_id' => $project->id,
            'vendor' => 'Jeep Spares Co',
            'bill_number' => 'JSC-778',
            'bill_date' => now()->subDays(7)->toDateString(),
            'stated_total' => 18500,
        ]);

        BillItem::query()->create([
            'bill_id' => $partsBill->id,
            'description' => 'Front bumper',
            'category' => BillItemCategory::Parts,
            'quantity' => 1,
            'unit_price' => 12500,
            'status' => BillItemStatus::Active,
        ]);

        BillItem::query()->create([
            'bill_id' => $partsBill->id,
            'description' => 'Helper worker (2 days)',
            'category' => BillItemCategory::Labor,
            'quantity' => 2,
            'unit_price' => 1500,
            'status' => BillItemStatus::Active,
        ]);
    }
}

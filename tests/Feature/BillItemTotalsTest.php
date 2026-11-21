<?php

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Enums\RefundedBy;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\RelationManagers\BillItemsRelationManager;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

it('calculates chargeable totals after returns and waivers', function () {
    $project = Project::factory()->create([
        'agreed_amount' => 10000,
    ]);

    $bill = Bill::factory()->create([
        'project_id' => $project->id,
    ]);

    BillItem::factory()->create([
        'bill_id' => $bill->id,
        'category' => BillItemCategory::Parts,
        'quantity' => 1,
        'unit_price' => 5000,
        'status' => BillItemStatus::Active,
    ]);

    BillItem::factory()->create([
        'bill_id' => $bill->id,
        'category' => BillItemCategory::Materials,
        'quantity' => 2,
        'unit_price' => 1000,
        'status' => BillItemStatus::Returned,
        'refunded_by' => RefundedBy::RestorationGuy,
    ]);

    BillItem::factory()->create([
        'bill_id' => $bill->id,
        'category' => BillItemCategory::Tools,
        'quantity' => 1,
        'unit_price' => 500,
        'status' => BillItemStatus::Waived,
    ]);

    expect($bill->grossTotal())->toBe(7500.0)
        ->and($bill->returnedTotal())->toBe(2000.0)
        ->and($bill->waivedTotal())->toBe(500.0)
        ->and($bill->chargeableTotal())->toBe(5000.0)
        ->and($project->refundedByRestorationGuyTotal())->toBe(2000.0);
});

it('allows the admin panel login page', function () {
    $this->get('/admin/login')->assertOk();
});

it('shows the settlement report to authenticated users', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'agreed_amount' => 75000,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/admin/expense-report')
        ->assertOk()
        ->assertSee('Settlement with guy')
        ->assertSee('Agreed')
        ->assertSee('75,000.00')
        ->assertSee('Purchases')
        ->assertSee('Charge guy')
        ->assertSee('Paid');

    expect($project->exists)->toBeTrue();
});

it('shows project items tab with bill filter on the project view page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['name' => 'Budget Jeep']);
    $bill = Bill::factory()->create([
        'project_id' => $project->id,
        'vendor' => 'Parts World',
    ]);
    $otherBill = Bill::factory()->create([
        'project_id' => $project->id,
        'vendor' => 'Paint Shop',
    ]);
    $item = BillItem::factory()->create([
        'bill_id' => $bill->id,
        'description' => 'Primer milk',
        'quantity' => 1,
        'unit_price' => 450,
        'status' => BillItemStatus::Active,
    ]);
    $otherItem = BillItem::factory()->create([
        'bill_id' => $otherBill->id,
        'description' => 'Sandpaper pack',
        'quantity' => 1,
        'unit_price' => 100,
        'status' => BillItemStatus::Active,
    ]);

    $this->actingAs($user)
        ->get('/admin/projects/'.$project->id)
        ->assertOk()
        ->assertSee('Items')
        ->assertSee('Bills');

    Livewire::actingAs($user)
        ->test(BillItemsRelationManager::class, [
            'ownerRecord' => $project,
            'pageClass' => ViewProject::class,
        ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$item, $otherItem])
        ->assertSee('Primer milk')
        ->assertSee('Parts World')
        ->filterTable('bill_id', $bill->id)
        ->assertCanSeeTableRecords([$item])
        ->assertCanNotSeeTableRecords([$otherItem]);
});

it('shows project budget cards on the bill view page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'name' => 'Jeep Restomod',
        'agreed_amount' => 12500,
    ]);
    $bill = Bill::factory()->create([
        'project_id' => $project->id,
        'vendor' => 'Auto Parts Hub',
    ]);

    BillItem::factory()->create([
        'bill_id' => $bill->id,
        'quantity' => 1,
        'unit_price' => 3000,
        'status' => BillItemStatus::Active,
    ]);

    $this->actingAs($user)
        ->get('/admin/bills/'.$bill->id)
        ->assertOk()
        ->assertSee('Project budget')
        ->assertSee('Jeep Restomod')
        ->assertSee('Agreed budget')
        ->assertSee('12,500.00')
        ->assertSee('Charge guy')
        ->assertSee('3,000.00');
});

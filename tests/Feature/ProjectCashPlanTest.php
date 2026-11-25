<?php

use App\Enums\BillItemStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\Projects\Widgets\PaymentProgressChart;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Project;
use App\Models\RestorationPayment;
use App\Models\User;
use Livewire\Livewire;

it('calculates need to save from agreed minus covered budget pieces', function () {
    $project = Project::factory()->create([
        'agreed_amount' => 45000,
        'cash_on_hand' => 20000,
        'cash_to_load' => 10000,
    ]);

    expect($project->availableCash())->toBe(30000.0)
        ->and($project->budgetCoveredTotal())->toBe(30000.0)
        ->and($project->cashStillNeeded())->toBe(15000.0)
        ->and($project->cashSurplus())->toBe(0.0);
});

it('counts paid to guy, parts spend, cash on hand, and loan toward the agreed budget', function () {
    $project = Project::factory()->create([
        'agreed_amount' => 2400000,
        'cash_on_hand' => 600000,
        'cash_to_load' => 1300000,
    ]);

    RestorationPayment::factory()->create([
        'project_id' => $project->id,
        'amount' => 560000,
        'method' => PaymentMethod::Cash,
    ]);

    $bill = Bill::factory()->create([
        'project_id' => $project->id,
    ]);

    BillItem::factory()->create([
        'bill_id' => $bill->id,
        'quantity' => 1,
        'unit_price' => 160160,
        'status' => BillItemStatus::Active,
    ]);

    expect($project->paidToRestorationGuyTotal())->toBe(560000.0)
        ->and($project->spentByMeTotal())->toBe(160160.0)
        ->and($project->availableCash())->toBe(1900000.0)
        ->and($project->budgetCoveredTotal())->toBe(2620160.0)
        ->and($project->cashStillNeeded())->toBe(0.0)
        ->and($project->cashSurplus())->toBe(220160.0);
});

it('reports surplus when covered total exceeds the agreed amount', function () {
    $project = Project::factory()->create([
        'agreed_amount' => 45000,
        'cash_on_hand' => 40000,
        'cash_to_load' => 10000,
    ]);

    expect($project->cashStillNeeded())->toBe(0.0)
        ->and($project->cashSurplus())->toBe(5000.0);
});

it('shows the cash plan and spent total on the project view page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'name' => 'Cash Plan Jeep',
        'agreed_amount' => 35000,
        'cash_on_hand' => 12000,
        'cash_to_load' => 8000,
    ]);

    $bill = Bill::factory()->create([
        'project_id' => $project->id,
    ]);

    BillItem::factory()->create([
        'bill_id' => $bill->id,
        'quantity' => 1,
        'unit_price' => 5000,
        'status' => BillItemStatus::Active,
    ]);

    $this->actingAs($user)
        ->get('/admin/projects/'.$project->id)
        ->assertOk()
        ->assertSee('Cash plan')
        ->assertSee('Cash on hand')
        ->assertSee('I already spent')
        ->assertSee('Total covered')
        ->assertSee('Need to save')
        ->assertSee('5,000.00')
        ->assertSee('10,000.00')
        ->assertSee('Payment progress');
});

it('shows surplus in green on the project view when covered exceeds agreed', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'name' => 'Tata Sumo',
        'agreed_amount' => 2400000,
        'cash_on_hand' => 600000,
        'cash_to_load' => 1300000,
    ]);

    RestorationPayment::factory()->create([
        'project_id' => $project->id,
        'amount' => 560000,
        'method' => PaymentMethod::Cash,
    ]);

    BillItem::factory()->create([
        'bill_id' => Bill::factory()->create([
            'project_id' => $project->id,
        ])->id,
        'quantity' => 1,
        'unit_price' => 160160,
        'status' => BillItemStatus::Active,
    ]);

    $this->actingAs($user)
        ->get('/admin/projects/'.$project->id)
        ->assertOk()
        ->assertSee('Surplus')
        ->assertSee('220,160.00')
        ->assertSee('Total covered')
        ->assertDontSee('Need to save');
});

it('charts paid vs remaining restoration payments on the project view', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'agreed_amount' => 40000,
    ]);

    RestorationPayment::factory()->create([
        'project_id' => $project->id,
        'amount' => 15000,
        'method' => PaymentMethod::Cash,
    ]);

    Livewire::actingAs($user)
        ->test(PaymentProgressChart::class, [
            'record' => $project,
        ])
        ->assertSuccessful()
        ->assertSee('Payment progress')
        ->assertSee('Paid to guy')
        ->assertSee('Still to pay');
});

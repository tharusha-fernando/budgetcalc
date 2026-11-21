<?php

use App\Enums\PaymentMethod;
use App\Filament\Resources\Projects\Widgets\PaymentProgressChart;
use App\Models\Project;
use App\Models\RestorationPayment;
use App\Models\User;
use Livewire\Livewire;

it('calculates how much cash still needs to be saved against agreed amount', function () {
    $project = Project::factory()->create([
        'agreed_amount' => 45000,
        'cash_on_hand' => 20000,
        'cash_to_load' => 10000,
    ]);

    expect($project->availableCash())->toBe(30000.0)
        ->and($project->cashStillNeeded())->toBe(15000.0)
        ->and($project->cashSurplus())->toBe(0.0);
});

it('reports surplus when available cash covers the agreed amount', function () {
    $project = Project::factory()->create([
        'agreed_amount' => 45000,
        'cash_on_hand' => 40000,
        'cash_to_load' => 10000,
    ]);

    expect($project->cashStillNeeded())->toBe(0.0)
        ->and($project->cashSurplus())->toBe(5000.0);
});

it('shows the cash plan on the project view page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'name' => 'Cash Plan Jeep',
        'agreed_amount' => 35000,
        'cash_on_hand' => 12000,
        'cash_to_load' => 8000,
    ]);

    $this->actingAs($user)
        ->get('/admin/projects/'.$project->id)
        ->assertOk()
        ->assertSee('Cash plan')
        ->assertSee('Cash on hand')
        ->assertSee('Need to save')
        ->assertSee('15,000.00')
        ->assertSee('Payment progress');
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

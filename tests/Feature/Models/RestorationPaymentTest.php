<?php

use App\Enums\PaymentMethod;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\RelationManagers\RestorationPaymentsRelationManager;
use App\Models\Project;
use App\Models\RestorationPayment;
use App\Models\User;
use Livewire\Livewire;

it('tracks direct payments against the agreed restoration amount', function () {
    $project = Project::factory()->create([
        'agreed_amount' => 50000,
    ]);

    RestorationPayment::factory()->create([
        'project_id' => $project->id,
        'amount' => 20000,
        'method' => PaymentMethod::Cash,
    ]);

    RestorationPayment::factory()->create([
        'project_id' => $project->id,
        'amount' => 15000,
        'method' => PaymentMethod::Upi,
    ]);

    expect($project->paidToRestorationGuyTotal())->toBe(35000.0)
        ->and($project->stillOwedToRestorationGuy())->toBe(15000.0);
});

it('records a cash payment from the project payments tab', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'agreed_amount' => 40000,
    ]);

    Livewire::actingAs($user)
        ->test(RestorationPaymentsRelationManager::class, [
            'ownerRecord' => $project,
            'pageClass' => ViewProject::class,
        ])
        ->callTableAction('create', data: [
            'amount' => 5000,
            'method' => PaymentMethod::Cash->value,
            'paid_on' => now()->toDateString(),
            'notes' => 'Cash advance',
        ])
        ->assertHasNoTableActionErrors();

    expect($project->fresh()->paidToRestorationGuyTotal())->toBe(5000.0)
        ->and(RestorationPayment::query()->where('project_id', $project->id)->count())->toBe(1);
});

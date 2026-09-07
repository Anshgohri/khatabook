<?php

use App\Enums\RoleName;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

test('sales today kpi only sums todays sales for the current scope', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Sale::factory()->create(['user_id' => $staff->id, 'date' => today(), 'quantity' => 2, 'unit_price' => 100]);
    Sale::factory()->create(['user_id' => $staff->id, 'date' => today()->subDays(2), 'quantity' => 5, 'unit_price' => 100]);
    Sale::factory()->create(['date' => today(), 'quantity' => 9, 'unit_price' => 100]);

    $component = Livewire::actingAs($staff)->test('pages::khatabook.dashboard');

    expect($component->get('salesToday'))->toBe(200.0);
});

test('manager sees sales from every user in the kpis', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $staff = User::factory()->role(RoleName::Staff)->create();

    Sale::factory()->create(['user_id' => $staff->id, 'date' => today(), 'quantity' => 1, 'unit_price' => 300]);
    Sale::factory()->create(['user_id' => $manager->id, 'date' => today(), 'quantity' => 1, 'unit_price' => 200]);

    $component = Livewire::actingAs($manager)->test('pages::khatabook.dashboard');

    expect($component->get('salesToday'))->toBe(500.0);
});

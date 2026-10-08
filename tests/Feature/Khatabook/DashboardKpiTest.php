<?php

use App\Enums\RoleName;
use App\Models\Sale;
use App\Models\User;

test('sales today kpi only sums todays sales for the current scope', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Sale::factory()->create(['user_id' => $staff->id, 'date' => today(), 'quantity' => 2, 'unit_price' => 100]);
    Sale::factory()->create(['user_id' => $staff->id, 'date' => today()->subDays(2), 'quantity' => 5, 'unit_price' => 100]);
    Sale::factory()->create(['date' => today(), 'quantity' => 9, 'unit_price' => 100]);

    $response = $this->actingAs($staff)->get(route('dashboard'));

    $response->assertViewHas('salesToday', 200.0);
});

test('manager sees sales from every user in the kpis', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $staff = User::factory()->role(RoleName::Staff)->create();

    Sale::factory()->create(['user_id' => $staff->id, 'date' => today(), 'quantity' => 1, 'unit_price' => 300]);
    Sale::factory()->create(['user_id' => $manager->id, 'date' => today(), 'quantity' => 1, 'unit_price' => 200]);

    $response = $this->actingAs($manager)->get(route('dashboard'));

    $response->assertViewHas('salesToday', 500.0);
});

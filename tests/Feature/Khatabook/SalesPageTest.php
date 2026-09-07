<?php

use App\Enums\RoleName;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('sales'))->assertRedirect(route('login'));
});

test('staff only see their own sales on the sales page', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();
    $ownSale = Sale::factory()->create(['user_id' => $staff->id]);
    Sale::factory()->create();

    $component = Livewire::actingAs($staff)->test('pages::khatabook.sales');

    expect($component->get('sales')->pluck('id')->all())->toEqual([$ownSale->id]);
});

test('viewer cannot create a sale', function () {
    $viewer = User::factory()->role(RoleName::Viewer)->create();

    Livewire::actingAs($viewer)
        ->test('pages::khatabook.sales')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Acme')
        ->set('items_sold', 'Bamboo Poles')
        ->set('quantity', 1)
        ->set('unit_price', 10)
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertForbidden();
});

test('staff can create a sale and the total is computed automatically', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Acme Traders')
        ->set('items_sold', 'Bamboo Poles')
        ->set('quantity', 10)
        ->set('unit_price', 50)
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::sole();

    expect($sale->user_id)->toBe($staff->id);
    expect((float) $sale->total_amount)->toBe(500.0);
});

test('staff cannot update a sale belonging to another user', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();
    $otherSale = Sale::factory()->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales')
        ->call('editSale', $otherSale->id)
        ->assertForbidden();
});

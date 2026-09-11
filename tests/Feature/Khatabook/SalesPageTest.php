<?php

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('sales'))->assertRedirect(route('login'));
    $this->get(route('sales.create'))->assertRedirect(route('login'));
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
        ->test('pages::khatabook.sales-form')
        ->assertForbidden();
});

test('staff can create a sale and the total is computed automatically', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Acme Traders')
        ->set('saleItems', [
            ['product_id' => '', 'quantity' => 10, 'unit_price' => 50, 'total_price' => 500],
        ])
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sales'));

    $sale = Sale::sole();

    expect($sale->user_id)->toBe($staff->id);
    expect((float) $sale->total_amount)->toBe(500.0);
});

test('staff can create multi-product sale and product stock levels are automatically reduced', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    $bans = Product::factory()->create(['name' => 'Bans 25 feet', 'unit_price' => 150, 'stock_level' => 50]);
    $ghodi = Product::factory()->create(['name' => 'Ghodi 4 feet', 'unit_price' => 200, 'stock_level' => 30]);
    $siddi = Product::factory()->create(['name' => 'Siddi 10 feet', 'unit_price' => 500, 'stock_level' => 20]);

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Rajesh Buildcon')
        ->set('saleItems', [
            ['product_id' => $bans->id, 'quantity' => 2, 'unit_price' => 150, 'total_price' => 300],
            ['product_id' => $ghodi->id, 'quantity' => 4, 'unit_price' => 200, 'total_price' => 800],
            ['product_id' => $siddi->id, 'quantity' => 2, 'unit_price' => 500, 'total_price' => 1000],
        ])
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::latest()->first();

    expect($sale->items)->toHaveCount(3);
    expect((float) $sale->total_amount)->toBe(2100.0);
    expect($sale->items_sold)->toContain('2x Bans 25 feet');

    // Verify stock reduction:
    expect($bans->fresh()->stock_level)->toBe(48); // 50 - 2
    expect($ghodi->fresh()->stock_level)->toBe(26); // 30 - 4
    expect($siddi->fresh()->stock_level)->toBe(18); // 20 - 2
});

test('deleting a sale restores product stock levels', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();
    $manager = User::factory()->role(RoleName::Manager)->create();
    $bans = Product::factory()->create(['name' => 'Bans 25 feet', 'unit_price' => 150, 'stock_level' => 50]);

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Test Customer')
        ->set('saleItems', [
            ['product_id' => $bans->id, 'quantity' => 5, 'unit_price' => 150, 'total_price' => 750],
        ])
        ->set('payment_status', 'paid')
        ->call('save');

    expect($bans->fresh()->stock_level)->toBe(45);

    $sale = Sale::latest()->first();

    Livewire::actingAs($manager)
        ->test('pages::khatabook.sales')
        ->call('deleteSale', $sale->id);

    expect($bans->fresh()->stock_level)->toBe(50);
});

test('discount reduces final sale total amount', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Discount Customer')
        ->set('saleItems', [
            ['product_id' => '', 'quantity' => 1, 'unit_price' => 1500, 'total_price' => 1500],
        ])
        ->set('discount', 200)
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::latest()->first();

    expect((float) $sale->discount)->toBe(200.0);
    expect((float) $sale->total_amount)->toBe(1300.0);
});

test('discount cannot be greater than subtotal amount', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Excessive Discount Customer')
        ->set('saleItems', [
            ['product_id' => '', 'quantity' => 1, 'unit_price' => 500, 'total_price' => 500],
        ])
        ->set('discount', 600)
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasErrors(['discount']);
});

test('saving a sale automatically creates or updates a user with ROLE_CUSTOMER', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Rahul Verma')
        ->set('customer_phone', '9876543210')
        ->set('customer_city', 'Jaipur')
        ->set('customer_address', '123 Station Road')
        ->set('saleItems', [
            ['product_id' => '', 'quantity' => 1, 'unit_price' => 500, 'total_price' => 500],
        ])
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors();

    $customerUser = User::where('name', 'Rahul Verma')->first();

    expect($customerUser)->not->toBeNull();
    expect($customerUser->phone)->toBe('9876543210');
    expect($customerUser->city)->toBe('Jaipur');
    expect($customerUser->address)->toBe('123 Station Road');
    expect($customerUser->isCustomer())->toBeTrue();

    $sale = Sale::latest()->first();
    expect($sale->customer_id)->toBe($customerUser->id);
});

test('staff cannot update a sale belonging to another user', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();
    $otherSale = Sale::factory()->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form', ['sale' => $otherSale])
        ->assertForbidden();
});

test('existing customers can be searched by name, mobile number, or email in sales form', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    $cust1 = User::factory()->role(RoleName::Customer)->create([
        'name' => 'Aarav Sharma',
        'phone' => '9876543210',
        'email' => 'aarav@example.com',
    ]);
    $cust2 = User::factory()->role(RoleName::Customer)->create([
        'name' => 'Bhavna Patel',
        'phone' => '9123456789',
        'email' => 'bhavna@test.com',
    ]);

    $component = Livewire::actingAs($staff)
        ->test('pages::khatabook.sales-form');

    // Search by name
    $component->set('customerSearch', 'Aarav');
    expect($component->get('existingCustomers')->pluck('id')->all())->toContain($cust1->id);
    expect($component->get('existingCustomers')->pluck('id')->all())->not->toContain($cust2->id);

    // Search by mobile number
    $component->set('customerSearch', '912345');
    expect($component->get('existingCustomers')->pluck('id')->all())->toContain($cust2->id);
    expect($component->get('existingCustomers')->pluck('id')->all())->not->toContain($cust1->id);

    // Search by email
    $component->set('customerSearch', 'aarav@example.com');
    expect($component->get('existingCustomers')->pluck('id')->all())->toContain($cust1->id);
    expect($component->get('existingCustomers')->pluck('id')->all())->not->toContain($cust2->id);

    // Select customer via selectCustomer
    $component->call('selectCustomer', $cust1->id);
    expect($component->get('customer_id'))->toBe((string) $cust1->id);
    expect($component->get('customer_name'))->toBe('Aarav Sharma');
    expect($component->get('customer_phone'))->toBe('9876543210');
    expect($component->get('customer_email'))->toBe('aarav@example.com');

    // Clear selection
    $component->call('clearCustomerSelection');
    expect($component->get('customer_id'))->toBe('');
    expect($component->get('customer_name'))->toBe('');
    expect($component->get('customer_phone'))->toBe('');
});

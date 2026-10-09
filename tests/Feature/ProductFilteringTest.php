<?php

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Livewire\Livewire;

test('dashboard products list filters by search, category, type, and stock level', function () {
    $user = User::factory()->role(RoleName::Admin)->create();
    $cat1 = ProductCategory::factory()->create(['name' => 'Furniture']);
    $cat2 = ProductCategory::factory()->create(['name' => 'Bamboo Raw']);

    $p1 = Product::factory()->create([
        'name' => 'Bamboo Chair Special',
        'product_category_id' => $cat1->id,
        'type' => 'finished_good',
        'stock_level' => 10,
    ]);

    $p2 = Product::factory()->create([
        'name' => 'Raw Bamboo Pole',
        'product_category_id' => $cat2->id,
        'type' => 'raw_material',
        'stock_level' => 0,
    ]);

    Livewire::actingAs($user)
        ->test('pages::products')
        ->set('search', 'Special')
        ->assertSee($p1->name)
        ->assertDontSee($p2->name)
        ->set('search', '')
        ->set('categoryId', (string) $cat2->id)
        ->assertSee($p2->name)
        ->assertDontSee($p1->name)
        ->set('categoryId', '')
        ->set('typeFilter', 'raw_material')
        ->assertSee($p2->name)
        ->assertDontSee($p1->name)
        ->set('typeFilter', '')
        ->set('stockFilter', 'out_of_stock')
        ->assertSee($p2->name)
        ->assertDontSee($p1->name);
});

test('website shop page filters by search keyword and category', function () {
    $cat1 = ProductCategory::factory()->create(['name' => 'Scaffolding']);
    $cat2 = ProductCategory::factory()->create(['name' => 'Decor']);

    $p1 = Product::factory()->create([
        'name' => 'Heavy Duty Scaffolding',
        'product_category_id' => $cat1->id,
        'unit_price' => 500,
    ]);

    $p2 = Product::factory()->create([
        'name' => 'Bamboo Vase Decor',
        'product_category_id' => $cat2->id,
        'unit_price' => 100,
    ]);

    $this->get(route('shop', ['search' => 'Scaffolding']))
        ->assertStatus(200)
        ->assertSee($p1->name)
        ->assertDontSee($p2->name);

    $this->get(route('shop', ['category' => $cat2->id]))
        ->assertStatus(200)
        ->assertSee($p2->name)
        ->assertDontSee($p1->name);
});

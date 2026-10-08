<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Livewire\Livewire;

test('expenses page filters by date preset today and yesterday', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    $todayExpense = Expense::factory()->create([
        'user_id' => $user->id,
        'expense_category_id' => $category->id,
        'description' => 'Today Expense Item',
        'date' => now()->toDateString(),
        'amount' => 500,
    ]);

    $yesterdayExpense = Expense::factory()->create([
        'user_id' => $user->id,
        'expense_category_id' => $category->id,
        'description' => 'Yesterday Expense Item',
        'date' => now()->subDay()->toDateString(),
        'amount' => 750,
    ]);

    // Default (All Time): shows both expenses
    Livewire::actingAs($user)
        ->test('pages::expenses')
        ->assertSee('Today Expense Item')
        ->assertSee('Yesterday Expense Item')
        // Filter Today
        ->set('datePreset', 'today')
        ->assertSet('dateFrom', now()->toDateString())
        ->assertSet('dateTo', now()->toDateString())
        ->assertSee('Today Expense Item')
        ->assertDontSee('Yesterday Expense Item')
        // Filter Yesterday
        ->set('datePreset', 'yesterday')
        ->assertSet('dateFrom', now()->subDay()->toDateString())
        ->assertSet('dateTo', now()->subDay()->toDateString())
        ->assertSee('Yesterday Expense Item')
        ->assertDontSee('Today Expense Item');
});

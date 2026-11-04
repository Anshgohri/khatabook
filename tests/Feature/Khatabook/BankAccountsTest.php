<?php

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

test('admin can view bank accounts passbook page', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('bank-accounts'));

    $response->assertStatus(200);
});

test('user can create a bank account and record credit and debit transactions', function () {
    $user = User::factory()->create();

    $account = BankAccount::create([
        'user_id' => $user->id,
        'bank_name' => 'HDFC Bank',
        'account_name' => 'Primary Current',
        'account_number' => '50200012345678',
        'opening_balance' => 10000.00,
        'current_balance' => 10000.00,
        'color' => 'emerald',
        'is_active' => true,
    ]);

    expect((float) $account->current_balance)->toBe(10000.00);

    // Record credit
    $credit = BankTransaction::create([
        'bank_account_id' => $account->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'credit',
        'amount' => 5000.00,
        'category' => 'Customer Payment',
        'party_name' => 'John Doe',
        'reference_number' => 'UTR123456',
    ]);

    $account->refresh();
    expect((float) $account->current_balance)->toBe(15000.00);

    // Record debit
    $debit = BankTransaction::create([
        'bank_account_id' => $account->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'debit',
        'amount' => 2000.00,
        'category' => 'Vendor Payment',
        'party_name' => 'Supplier Co',
        'reference_number' => 'CHQ987654',
    ]);

    $account->refresh();
    expect((float) $account->current_balance)->toBe(13000.00);

    // Delete debit
    $debit->delete();
    $account->refresh();
    expect((float) $account->current_balance)->toBe(15000.00);
});

test('livewire component can record credit transaction with receipt', function () {
    $user = User::factory()->create();

    $account = BankAccount::create([
        'user_id' => $user->id,
        'bank_name' => 'ICICI Bank',
        'account_name' => 'Operations',
        'opening_balance' => 0.00,
        'current_balance' => 0.00,
    ]);

    $file = UploadedFile::fake()->image('deposit_slip.jpg');

    Livewire::actingAs($user)
        ->test('pages::bank-accounts')
        ->set('txAccountId', $account->id)
        ->set('txType', 'credit')
        ->set('txDate', now()->toDateString())
        ->set('txAmount', 25000)
        ->set('txCategory', 'Customer Payment')
        ->set('txPartyName', 'Acme Corp')
        ->set('txReferenceNumber', 'IMPS-998877')
        ->set('txDescription', 'Advance payment for bulk order')
        ->set('txReceipt', $file)
        ->call('saveTransaction');

    $tx = BankTransaction::where('bank_account_id', $account->id)->first();
    expect($tx)->not->toBeNull();
    expect((float) $tx->amount)->toBe(25000.00);
    expect($tx->receipt_path)->not->toBeNull();

    $account->refresh();
    expect((float) $account->current_balance)->toBe(25000.00);
});

test('internal transfer moves money between accounts correctly', function () {
    $user = User::factory()->create();

    $hdfc = BankAccount::create([
        'user_id' => $user->id,
        'bank_name' => 'HDFC Bank',
        'account_name' => 'Main Current',
        'opening_balance' => 50000.00,
        'current_balance' => 50000.00,
    ]);

    $sbi = BankAccount::create([
        'user_id' => $user->id,
        'bank_name' => 'SBI Bank',
        'account_name' => 'Reserve A/C',
        'opening_balance' => 10000.00,
        'current_balance' => 10000.00,
    ]);

    Livewire::actingAs($user)
        ->test('pages::bank-accounts')
        ->set('transferFromAccountId', $hdfc->id)
        ->set('transferToAccountId', $sbi->id)
        ->set('transferDate', now()->toDateString())
        ->set('transferAmount', 15000)
        ->set('transferReference', 'NEFT-554433')
        ->set('transferNotes', 'Funds rebalance')
        ->call('executeTransfer');

    $hdfc->refresh();
    $sbi->refresh();

    expect((float) $hdfc->current_balance)->toBe(35000.00);
    expect((float) $sbi->current_balance)->toBe(25000.00);
});

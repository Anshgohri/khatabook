<?php

use App\Enums\RoleName;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

test('exporting sales streams a csv scoped to the current user for staff', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();
    $ownSale = Sale::factory()->create([
        'user_id' => $staff->id,
        'date' => now(),
        'customer_name' => 'Own Customer',
    ]);
    Sale::factory()->create(['date' => now(), 'customer_name' => 'Other Customer']);

    $response = Livewire::actingAs($staff)
        ->test('pages::khatabook.reports')
        ->call('exportSales');

    $response->assertFileDownloaded();

    $content = base64_decode(data_get($response->effects, 'download.content'));

    expect($content)->toContain('Own Customer');
    expect($content)->not->toContain('Other Customer');
});

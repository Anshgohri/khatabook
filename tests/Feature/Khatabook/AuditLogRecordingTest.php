<?php

use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Sale;
use App\Models\User;

test('creating, updating, and deleting a sale writes audit log entries', function () {
    $user = User::factory()->role(RoleName::Staff)->create();
    $this->actingAs($user);

    $sale = Sale::factory()->create(['user_id' => $user->id]);

    expect(AuditLog::query()->where('auditable_type', Sale::class)->where('auditable_id', $sale->id)->where('action', 'created')->exists())->toBeTrue();

    $sale->update(['customer_name' => 'Updated Customer']);

    $updateLog = AuditLog::query()->where('auditable_type', Sale::class)->where('auditable_id', $sale->id)->where('action', 'updated')->sole();

    expect($updateLog->new_values)->toMatchArray(['customer_name' => 'Updated Customer']);
    expect($updateLog->user_id)->toBe($user->id);

    $sale->delete();

    expect(AuditLog::query()->where('auditable_type', Sale::class)->where('auditable_id', $sale->id)->where('action', 'deleted')->exists())->toBeTrue();
});

test('updating only the timestamp does not create a noisy audit entry', function () {
    $user = User::factory()->role(RoleName::Staff)->create();
    $this->actingAs($user);

    $sale = Sale::factory()->create(['user_id' => $user->id]);
    $countBefore = AuditLog::query()->count();

    $sale->touch();

    expect(AuditLog::query()->count())->toBe($countBefore);
});

<?php

use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\User;

test('only admin and manager can view the audit log', function () {
    expect(User::factory()->role(RoleName::Admin)->create()->can('viewAny', AuditLog::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Manager)->create()->can('viewAny', AuditLog::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Staff)->create()->can('viewAny', AuditLog::class))->toBeFalse();
    expect(User::factory()->role(RoleName::Viewer)->create()->can('viewAny', AuditLog::class))->toBeFalse();
});

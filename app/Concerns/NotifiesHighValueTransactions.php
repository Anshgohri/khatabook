<?php

namespace App\Concerns;

use App\Enums\RoleName;
use App\Models\User;
use App\Notifications\HighValueTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

trait NotifiesHighValueTransactions
{
    protected static function notifyIfHighValue(string $type, Model $transaction, float $amount): void
    {
        if ($amount < (float) config('khatabook.high_value_threshold')) {
            return;
        }

        $recipients = User::query()
            ->whereHas('role', fn ($query) => $query->whereIn('name', [RoleName::Admin->value, RoleName::Manager->value]))
            ->get();

        Notification::send($recipients, new HighValueTransaction($type, $transaction, $amount));
    }
}

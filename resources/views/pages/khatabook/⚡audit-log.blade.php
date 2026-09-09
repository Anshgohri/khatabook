<?php

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Audit Log')] class extends Component {
    use WithPagination;

    #[Url]
    public string $userId = '';

    #[Url]
    public string $action = '';

    public function mount(): void
    {
        $this->authorize('viewAny', AuditLog::class);
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['userId', 'action'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function actors()
    {
        return User::query()->orderBy('name')->get();
    }

    #[Computed]
    public function logs()
    {
        return AuditLog::query()
            ->with('user')
            ->when($this->userId, fn ($query) => $query->where('user_id', $this->userId))
            ->when($this->action, fn ($query) => $query->where('action', $this->action))
            ->latest('created_at')
            ->paginate(20);
    }
}; ?>

<div class="flex flex-col gap-6">
    <flux:heading size="xl">{{ __('Audit Log') }}</flux:heading>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:select wire:model.live="userId" :placeholder="__('All users')">
            <flux:select.option value="">{{ __('All users') }}</flux:select.option>
            @foreach ($this->actors as $actor)
                <flux:select.option value="{{ $actor->id }}">{{ $actor->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="action" :placeholder="__('All actions')">
            <flux:select.option value="">{{ __('All actions') }}</flux:select.option>
            <flux:select.option value="created">{{ __('Created') }}</flux:select.option>
            <flux:select.option value="updated">{{ __('Updated') }}</flux:select.option>
            <flux:select.option value="deleted">{{ __('Deleted') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->logs">
            <flux:table.columns>
                <flux:table.column>{{ __('When') }}</flux:table.column>
                <flux:table.column>{{ __('User') }}</flux:table.column>
                <flux:table.column>{{ __('Action') }}</flux:table.column>
                <flux:table.column>{{ __('Resource') }}</flux:table.column>
                <flux:table.column>{{ __('Changes') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->logs as $log)
                    <flux:table.row wire:key="audit-{{ $log->id }}">
                        <flux:table.cell>{{ $log->created_at->format('d M Y H:i') }}</flux:table.cell>
                        <flux:table.cell>{{ $log->user?->name ?? __('System') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="match ($log->action) { 'created' => 'green', 'updated' => 'amber', default => 'red' }" size="sm">
                                {{ ucfirst($log->action) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</flux:table.cell>
                        <flux:table.cell class="max-w-md truncate">
                            @if ($log->new_values)
                                {{ collect($log->new_values)->keys()->implode(', ') }}
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">{{ __('No audit entries found.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>

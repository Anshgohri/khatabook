<?php

use App\Models\Rental;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Rentals')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        // Add policy logic later if needed
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function rentals()
    {
        $user = Auth::user();

        return Rental::query()
            ->with(['user', 'customer', 'items.product'])
            ->when($this->search, function ($query) {
                $query->where('customer_name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                      ->orWhereHas('customer', function ($q) {
                          $q->where('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%");
                      });
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->latest('rented_at')
            ->paginate(15);
    }

    public function deleteRental(int $rentalId): void
    {
        $rental = Rental::findOrFail($rentalId);
        $rental->delete();
        unset($this->rentals);
        Flux::toast(variant: 'success', text: __('Rental deleted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:heading size="xl">{{ __('Rentals') }}</flux:heading>

        <div class="flex items-center gap-2 flex-wrap">
            <flux:button variant="primary" icon="plus" :href="route('rentals.create')" wire:navigate>{{ __('Add rental') }}</flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search customer...')" icon="magnifying-glass" />

        <flux:select wire:model.live="status" :placeholder="__('All statuses')">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
            <flux:select.option value="returned">{{ __('Returned') }}</flux:select.option>
            <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->rentals">
            <flux:table.columns>
                <flux:table.column>{{ __('Rented At') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Security') }}</flux:table.column>
                <flux:table.column>{{ __('Total Rent') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->rentals as $rental)
                <flux:table.row wire:key="rental-{{ $rental->id }}">
                    <flux:table.cell>{{ $rental->rented_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $rental->customer_name }}</span>
                            @if ($rental->customer_phone)
                            <span class="text-xs text-zinc-500">
                                {{ $rental->customer_phone }}
                            </span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>₹{{ number_format((float) $rental->security_deposit, 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                ₹{{ number_format((float) $rental->total_rent, 2) }}
                            </span>
                            @if ((float) $rental->discount > 0)
                            <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                (Disc: ₹{{ number_format((float) $rental->discount, 2) }})
                            </span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match ($rental->status) { 'returned' => 'green', 'active' => 'amber', default => 'red' }" size="sm">
                            {{ ucfirst($rental->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button size="sm" variant="subtle" icon="pencil" :href="route('rentals.edit', $rental->id)" wire:navigate>{{ __('Edit / Return') }}</flux:button>
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteRental({{ $rental->id }})" wire:confirm="{{ __('Delete this rental?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-500">{{ __('No rentals found.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
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
        <div>
            <flux:heading size="xl">{{ __('Rentals') }}</flux:heading>
            <flux:subheading size="sm">{{ __('Manage rental equipment, active customer dispatches, and returns.') }}</flux:subheading>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <flux:button variant="primary" icon="plus" :href="route('rentals.create')" wire:navigate class="w-full sm:w-auto">
                {{ __('Add rental') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search customer or phone...')" icon="magnifying-glass" />

        <flux:select wire:model.live="status" :placeholder="__('All statuses')">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
            <flux:select.option value="returned">{{ __('Returned') }}</flux:select.option>
            <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
        </flux:select>
    </div>

    <!-- Mobile Screen View (Cards) -->
    <div class="flex flex-col gap-3 md:hidden">
        @forelse ($this->rentals as $rental)
        <flux:card class="flex flex-col gap-3" wire:key="rental-card-{{ $rental->id }}">
            <div class="flex items-start justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-2">
                <div>
                    <div class="font-semibold text-zinc-900 dark:text-zinc-100 text-base">{{ $rental->customer_name }}</div>
                    @if ($rental->customer_phone)
                    <a href="tel:{{ $rental->customer_phone }}" class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 mt-0.5 inline-block">
                        📞 {{ $rental->customer_phone }}
                    </a>
                    @endif
                </div>
                <flux:badge :color="match ($rental->status) { 'returned' => 'green', 'active' => 'amber', default => 'red' }" size="sm">
                    {{ ucfirst($rental->status) }}
                </flux:badge>
            </div>

            <!-- Rented Products Box -->
            <div class="bg-zinc-50 dark:bg-zinc-800/40 rounded-lg p-2.5 flex flex-col gap-1.5 text-xs">
                <span class="font-semibold text-zinc-500 uppercase tracking-wider text-[10px]">{{ __('Rented Products') }} ({{ $rental->items->count() }})</span>
                @forelse ($rental->items as $item)
                    <div class="flex items-center justify-between text-zinc-800 dark:text-zinc-200">
                        <span class="font-medium">• {{ $item->product?->name ?? __('Unknown Product') }}</span>
                        <span class="text-zinc-500">Qty: {{ $item->quantity }} @ ₹{{ number_format((float) $item->daily_rent, 2) }}/d</span>
                    </div>
                @empty
                    <span class="text-zinc-400 italic">{{ __('No items recorded') }}</span>
                @endforelse
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs py-1 border-y border-zinc-100 dark:border-zinc-800">
                <div>
                    <span class="text-zinc-400 block">{{ __('Rented Date:') }}</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $rental->rented_at?->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block">{{ __('Returned:') }}</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $rental->returned_at ? $rental->returned_at->format('d M Y') : __('Active') }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block">{{ __('Security Deposit:') }}</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">₹{{ number_format((float) $rental->security_deposit, 2) }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block">{{ __('Total Rent:') }}</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">₹{{ number_format((float) $rental->total_rent, 2) }}</span>
                    @if ((float) $rental->discount > 0)
                    <span class="text-[10px] text-amber-600 block">(Disc: ₹{{ number_format((float) $rental->discount, 2) }})</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <flux:button size="sm" variant="subtle" icon="pencil" :href="route('rentals.edit', $rental->id)" wire:navigate class="flex-1">
                    {{ __('Edit / Return') }}
                </flux:button>
                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" wire:click="deleteRental({{ $rental->id }})" wire:confirm="{{ __('Delete this rental?') }}" />
            </div>
        </flux:card>
        @empty
        <div class="p-8 text-center text-zinc-500 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
            {{ __('No rentals found.') }}
        </div>
        @endforelse

        <div class="pt-2">
            {{ $this->rentals->links() }}
        </div>
    </div>

    <!-- Desktop Screen View (Table) -->
    <div class="hidden md:block w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->rentals">
            <flux:table.columns>
                <flux:table.column>{{ __('Rented At') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Rented Products') }}</flux:table.column>
                <flux:table.column>{{ __('Security') }}</flux:table.column>
                <flux:table.column>{{ __('Total Rent') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->rentals as $rental)
                <flux:table.row wire:key="rental-row-{{ $rental->id }}">
                    <flux:table.cell class="whitespace-nowrap">{{ $rental->rented_at?->format('d M Y') }}</flux:table.cell>
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
                    <flux:table.cell>
                        <div class="flex flex-col gap-1 max-w-xs">
                            @forelse ($rental->items as $item)
                                <span class="text-xs text-zinc-700 dark:text-zinc-300">
                                    • {{ $item->product?->name ?? __('Unknown Item') }}
                                    <span class="text-zinc-400 font-mono text-[11px]">(x{{ $item->quantity }} @ ₹{{ number_format((float) $item->daily_rent, 2) }}/d)</span>
                                </span>
                            @empty
                                <span class="text-xs text-zinc-400 italic">{{ __('None') }}</span>
                            @endforelse
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">₹{{ number_format((float) $rental->security_deposit, 2) }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
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
                    <flux:table.cell colspan="7" class="text-center text-zinc-500">{{ __('No rentals found.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
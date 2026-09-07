<?php

use App\Models\Sale;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Sales')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $paymentStatus = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $date = '';

    public string $customer_name = '';

    public string $items_sold = '';

    public int $quantity = 1;

    public float $unit_price = 0;

    public string $payment_status = 'paid';

    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Sale::class);
        $this->date = now()->toDateString();
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'paymentStatus', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function sales()
    {
        $user = Auth::user();

        return Sale::query()
            ->with('user')
            ->when($user->isStaff(), fn ($query) => $query->where('user_id', $user->id))
            ->when($this->search, fn ($query) => $query->where('customer_name', 'like', "%{$this->search}%"))
            ->when($this->paymentStatus, fn ($query) => $query->where('payment_status', $this->paymentStatus))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->latest('date')
            ->paginate(15);
    }

    public function createSale(): void
    {
        $this->authorize('create', Sale::class);

        $this->reset(['editingId', 'customer_name', 'items_sold', 'quantity', 'unit_price', 'notes']);
        $this->date = now()->toDateString();
        $this->payment_status = 'paid';
        $this->showForm = true;
    }

    public function editSale(int $saleId): void
    {
        $sale = Sale::findOrFail($saleId);

        $this->authorize('update', $sale);

        $this->editingId = $sale->id;
        $this->date = $sale->date->toDateString();
        $this->customer_name = $sale->customer_name;
        $this->items_sold = $sale->items_sold;
        $this->quantity = $sale->quantity;
        $this->unit_price = (float) $sale->unit_price;
        $this->payment_status = $sale->payment_status;
        $this->notes = (string) $sale->notes;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'items_sold' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:paid,partial,unpaid'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->editingId) {
            $sale = Sale::findOrFail($this->editingId);
            $this->authorize('update', $sale);
            $sale->update([...$validated, 'total_amount' => $validated['quantity'] * $validated['unit_price']]);
        } else {
            $this->authorize('create', Sale::class);
            Sale::create([...$validated, 'user_id' => Auth::id()]);
        }

        $this->showForm = false;
        unset($this->sales);
        Flux::toast(variant: 'success', text: __('Sale saved.'));
    }

    public function deleteSale(int $saleId): void
    {
        $sale = Sale::findOrFail($saleId);

        $this->authorize('delete', $sale);

        $sale->delete();
        unset($this->sales);
        Flux::toast(variant: 'success', text: __('Sale deleted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Sales') }}</flux:heading>

        @can('create', Sale::class)
            <flux:button variant="primary" icon="plus" wire:click="createSale">{{ __('Add sale') }}</flux:button>
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search customer...')" icon="magnifying-glass" />

        <flux:select wire:model.live="paymentStatus" :placeholder="__('All payment statuses')">
            <flux:select.option value="">{{ __('All payment statuses') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
            <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
            <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
        </flux:select>

        <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
        <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
    </div>

    <flux:table :paginate="$this->sales">
        <flux:table.columns>
            <flux:table.column>{{ __('Date') }}</flux:table.column>
            <flux:table.column>{{ __('Customer') }}</flux:table.column>
            <flux:table.column>{{ __('Items') }}</flux:table.column>
            <flux:table.column>{{ __('Qty') }}</flux:table.column>
            <flux:table.column>{{ __('Total') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Recorded by') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->sales as $sale)
                <flux:table.row wire:key="sale-{{ $sale->id }}">
                    <flux:table.cell>{{ $sale->date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>{{ $sale->customer_name }}</flux:table.cell>
                    <flux:table.cell>{{ $sale->items_sold }}</flux:table.cell>
                    <flux:table.cell>{{ $sale->quantity }}</flux:table.cell>
                    <flux:table.cell>{{ number_format((float) $sale->total_amount, 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match ($sale->payment_status) { 'paid' => 'green', 'partial' => 'amber', default => 'red' }" size="sm">
                            {{ ucfirst($sale->payment_status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $sale->user->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $sale)
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editSale({{ $sale->id }})" />
                            @endcan
                            @can('delete', $sale)
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteSale({{ $sale->id }})" wire:confirm="{{ __('Delete this sale?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-zinc-500">{{ __('No sales found.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal wire:model.self="showForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingId ? __('Edit sale') : __('Add sale') }}</flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input type="date" wire:model="date" :label="__('Date')" required />
                <flux:input wire:model="customer_name" :label="__('Customer name')" required />
                <flux:input wire:model="items_sold" :label="__('Items sold')" required />
                <flux:input type="number" min="1" wire:model="quantity" :label="__('Quantity')" required />
                <flux:input type="number" step="0.01" min="0" wire:model="unit_price" :label="__('Unit price')" required />

                <flux:select wire:model="payment_status" :label="__('Payment status')">
                    <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                    <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
                    <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

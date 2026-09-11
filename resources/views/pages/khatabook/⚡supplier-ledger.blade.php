<?php

use App\Models\Supplier;
use App\Models\SupplierPayment;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Supplier Ledger')] class extends Component {
    use WithFileUploads, WithPagination;

    public Supplier $supplier;

    #[Url]
    public string $period = 'this_month';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $search = '';

    // Payment/Purchase Modal State
    public bool $showPaymentModal = false;
    public string $type = 'raw_material_purchase';
    public float $amount = 0.0;
    public string $payment_method = 'cash';
    public string $payment_date = '';
    public string $payment_notes = '';
    public $bill_image = null;

    public function mount(Supplier $supplier): void
    {
        $this->authorize('update', $supplier);
        $this->supplier = $supplier;
        $this->payment_date = now()->toDateString();
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['period', 'typeFilter', 'search'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function payments()
    {
        return SupplierPayment::query()
            ->where('supplier_id', $this->supplier->id)
            ->when($this->period === 'this_week', fn ($q) => $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->period === 'this_month', fn ($q) => $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]))
            ->when($this->period === 'this_year', fn ($q) => $q->whereBetween('date', [now()->startOfYear(), now()->endOfYear()]))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn ($q) => $q->where(function ($sub) {
                $sub->where('notes', 'like', "%{$this->search}%")
                    ->orWhere('payment_method', 'like', "%{$this->search}%")
                    ->orWhere('amount', 'like', "%{$this->search}%");
            }))
            ->latest('date')
            ->latest('id')
            ->paginate(15);
    }

    #[Computed]
    public function purchasesInPeriod(): float
    {
        return (float) SupplierPayment::query()
            ->where('supplier_id', $this->supplier->id)
            ->where('type', 'raw_material_purchase')
            ->when($this->period === 'this_week', fn ($q) => $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->period === 'this_month', fn ($q) => $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]))
            ->when($this->period === 'this_year', fn ($q) => $q->whereBetween('date', [now()->startOfYear(), now()->endOfYear()]))
            ->sum('amount');
    }

    #[Computed]
    public function paymentsInPeriod(): float
    {
        return (float) SupplierPayment::query()
            ->where('supplier_id', $this->supplier->id)
            ->where('type', 'payment_made')
            ->when($this->period === 'this_week', fn ($q) => $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->period === 'this_month', fn ($q) => $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]))
            ->when($this->period === 'this_year', fn ($q) => $q->whereBetween('date', [now()->startOfYear(), now()->endOfYear()]))
            ->sum('amount');
    }

    public function openPaymentModal(string $defaultType = 'raw_material_purchase'): void
    {
        $this->authorize('update', $this->supplier);

        $this->type = $defaultType;
        $this->amount = 0.0;
        $this->payment_method = 'cash';
        $this->payment_date = now()->toDateString();
        $this->payment_notes = '';
        $this->bill_image = null;
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $this->authorize('update', $this->supplier);

        $validated = $this->validate([
            'type' => ['required', 'in:raw_material_purchase,payment_made'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,bank_transfer,cheque,other'],
            'payment_date' => ['required', 'date'],
            'payment_notes' => ['nullable', 'string'],
            'bill_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $billPath = null;
        if ($this->bill_image) {
            $billPath = $this->bill_image->store('bills', 'public');
        }

        SupplierPayment::create([
            'supplier_id' => $this->supplier->id,
            'user_id' => Auth::id(),
            'date' => $validated['payment_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['payment_notes'],
            'bill_path' => $billPath,
        ]);

        $this->supplier->refresh();
        $this->showPaymentModal = false;
        $this->bill_image = null;
        unset($this->payments, $this->purchasesInPeriod, $this->paymentsInPeriod);
        Flux::toast(variant: 'success', text: __('Supplier entry recorded successfully.'));
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = SupplierPayment::where('supplier_id', $this->supplier->id)->findOrFail($paymentId);
        $this->authorize('update', $this->supplier);

        $payment->delete();
        $this->supplier->refresh();
        unset($this->payments, $this->purchasesInPeriod, $this->paymentsInPeriod);
        Flux::toast(variant: 'success', text: __('Entry deleted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('suppliers')" wire:navigate>
                {{ __('Back to Suppliers') }}
            </flux:button>
            <div>
                <flux:heading size="xl">{{ $this->supplier->name }} – {{ __('Ledger History') }}</flux:heading>
                <flux:text class="mt-0.5 text-sm">
                    {{ __('Material:') }} {{ $this->supplier->material_supplied ?? __('Raw Material') }}
                    @if ($this->supplier->location)
                        • {{ $this->supplier->location }}
                    @endif
                    @if ($this->supplier->phone)
                        • {{ $this->supplier->phone }}
                    @endif
                </flux:text>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ((float) $this->supplier->outstanding_balance > 0)
                <flux:badge color="red" size="lg">Balance Owed: ₹{{ number_format((float) $this->supplier->outstanding_balance, 2) }}</flux:badge>
            @else
                <flux:badge color="zinc" size="lg">₹0.00 Balance</flux:badge>
            @endif

            <flux:button variant="primary" icon="plus" wire:click="openPaymentModal('raw_material_purchase')">
                {{ __('Record Purchase / Pay') }}
            </flux:button>
        </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Purchases (Selected Period)') }}</flux:text>
            <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($this->purchasesInPeriod, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Paid to Supplier (Period)') }}</flux:text>
            <flux:heading size="lg" class="text-green-600 dark:text-green-400">₹{{ number_format($this->paymentsInPeriod, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Net Outstanding Balance Owed') }}</flux:text>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">₹{{ number_format((float) $this->supplier->outstanding_balance, 2) }}</flux:heading>
        </flux:card>
    </div>

    <!-- Filter Bar -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search notes or amount...')" icon="magnifying-glass" />

        <flux:select wire:model.live="period">
            <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
            <flux:select.option value="this_week">{{ __('This Week') }}</flux:select.option>
            <flux:select.option value="this_year">{{ __('This Year') }}</flux:select.option>
            <flux:select.option value="all">{{ __('All Time') }}</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="typeFilter" :placeholder="__('All Transaction Types')">
            <flux:select.option value="">{{ __('All Transaction Types') }}</flux:select.option>
            <flux:select.option value="raw_material_purchase">{{ __('Raw Material Purchase') }}</flux:select.option>
            <flux:select.option value="payment_made">{{ __('Payment Made') }}</flux:select.option>
        </flux:select>

        <div class="flex items-center gap-2 sm:justify-end">
            <flux:button size="sm" variant="subtle" icon="shopping-bag" wire:click="openPaymentModal('raw_material_purchase')">
                {{ __('Purchase') }}
            </flux:button>
            <flux:button size="sm" variant="subtle" icon="banknotes" wire:click="openPaymentModal('payment_made')">
                {{ __('Pay') }}
            </flux:button>
        </div>
    </div>

    <!-- Paginated Ledger Table -->
    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->payments">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Transaction Type') }}</flux:table.column>
                <flux:table.column>{{ __('Amount') }}</flux:table.column>
                <flux:table.column>{{ __('Payment Method') }}</flux:table.column>
                <flux:table.column>{{ __('Notes / Item Details') }}</flux:table.column>
                <flux:table.column>{{ __('Bill / Receipt') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->payments as $payment)
                <flux:table.row wire:key="payment-{{ $payment->id }}">
                    <flux:table.cell class="font-medium">{{ $payment->date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($payment->type === 'raw_material_purchase')
                            <flux:badge color="orange" size="sm">{{ __('Purchase Invoice') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ __('Payment Made') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-semibold text-zinc-900 dark:text-zinc-100">
                        ₹{{ number_format((float) $payment->amount, 2) }}
                    </flux:table.cell>
                    <flux:table.cell class="uppercase text-xs font-medium">{{ $payment->payment_method }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-xs max-w-xs truncate" title="{{ $payment->notes }}">
                        {{ $payment->notes ?? '-' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-xs">
                        @if ($payment->bill_path)
                            <a href="{{ Storage::url($payment->bill_path) }}" target="_blank" class="inline-flex items-center gap-1 font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                📄 {{ __('View Receipt') }}
                            </a>
                        @else
                            <span class="text-zinc-400">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex justify-end">
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deletePayment({{ $payment->id }})" wire:confirm="{{ __('Delete this entry?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center text-zinc-500 py-6">{{ __('No transaction records found matching the criteria.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Record Purchase / Payment Modal -->
    <flux:modal wire:model.self="showPaymentModal" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Record Supplier Purchase / Payment') }}</flux:heading>

            <form wire:submit="savePayment" class="flex flex-col gap-4">
                <flux:select wire:model="type" :label="__('Transaction Type')" required>
                    <flux:select.option value="raw_material_purchase">{{ __('Raw Material Purchase (Increases Balance Owed)') }}</flux:select.option>
                    <flux:select.option value="payment_made">{{ __('Payment Paid to Supplier (Reduces Balance Owed)') }}</flux:select.option>
                </flux:select>

                <flux:input type="number" step="0.01" min="0.01" wire:model="amount" :label="__('Amount (₹)')" placeholder="e.g. 15000" required />
                <flux:input type="date" wire:model="payment_date" :label="__('Date')" required />

                <flux:select wire:model="payment_method" :label="__('Payment Method')">
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="upi">{{ __('UPI / PhonePe / GPay') }}</flux:select.option>
                    <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                    <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="payment_notes" :label="__('Notes / Item Details')" placeholder="e.g. 200 Bans purchased" rows="2" />

                <flux:input type="file" wire:model="bill_image" :label="__('Bill / Receipt Image (Optional)')" accept="image/*,.pdf" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showPaymentModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Entry') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

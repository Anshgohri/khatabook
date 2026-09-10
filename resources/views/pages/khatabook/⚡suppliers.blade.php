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

new #[Title('Suppliers')] class extends Component {
    use WithFileUploads, WithPagination;

    #[Url]
    public string $search = '';

    // Supplier Modal State
    public bool $showSupplierModal = false;
    public ?int $editingSupplierId = null;
    public string $name = '';
    public string $phone = '';
    public string $location = '';
    public string $material_supplied = '';
    public float $initial_balance = 0.0;
    public string $status = 'active';
    public string $notes = '';

    // Payment/Purchase Modal State
    public bool $showPaymentModal = false;
    public ?int $paymentSupplierId = null;
    public string $type = 'raw_material_purchase'; // raw_material_purchase, payment_made
    public float $amount = 0.0;
    public string $payment_method = 'cash';
    public string $payment_date = '';
    public string $payment_notes = '';
    public $bill_image = null;

    // Ledger Modal State
    public bool $showLedgerModal = false;
    public ?int $ledgerSupplierId = null;
    public string $ledgerPeriod = 'this_month'; // this_month, this_week, this_year, all

    public function mount(): void
    {
        $this->authorize('viewAny', Supplier::class);
        $this->payment_date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function purchasesThisMonth(): float
    {
        $user = Auth::user();
        return (float) SupplierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'raw_material_purchase')
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    }

    #[Computed]
    public function paymentsThisMonth(): float
    {
        $user = Auth::user();
        return (float) SupplierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'payment_made')
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    }

    #[Computed]
    public function totalOutstandingBalance(): float
    {
        $user = Auth::user();
        return (float) Supplier::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->sum('outstanding_balance');
    }

    #[Computed]
    public function suppliers()
    {
        $user = Auth::user();

        return Supplier::query()
            ->with(['payments'])
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->orWhere('material_supplied', 'like', "%{$this->search}%")
                ->orWhere('location', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function selectedLedgerSupplier()
    {
        if (! $this->ledgerSupplierId) {
            return null;
        }

        return Supplier::with(['payments' => function ($query) {
            $query->when($this->ledgerPeriod === 'this_week', fn ($q) => $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]))
                ->when($this->ledgerPeriod === 'this_month', fn ($q) => $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]))
                ->when($this->ledgerPeriod === 'this_year', fn ($q) => $q->whereBetween('date', [now()->startOfYear(), now()->endOfYear()]))
                ->latest('date');
        }])->find($this->ledgerSupplierId);
    }

    public function createSupplier(): void
    {
        $this->authorize('create', Supplier::class);

        $this->reset(['editingSupplierId', 'name', 'phone', 'location', 'material_supplied', 'initial_balance', 'notes']);
        $this->status = 'active';
        $this->showSupplierModal = true;
    }

    public function editSupplier(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->authorize('update', $supplier);

        $this->editingSupplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->phone = (string) $supplier->phone;
        $this->location = (string) $supplier->location;
        $this->material_supplied = (string) $supplier->material_supplied;
        $this->initial_balance = 0.0;
        $this->status = $supplier->status;
        $this->notes = (string) $supplier->notes;
        $this->showSupplierModal = true;
    }

    public function saveSupplier(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'material_supplied' => ['nullable', 'string', 'max:255'],
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $initialBalance = (float) ($validated['initial_balance'] ?? 0.0);
        unset($validated['initial_balance']);

        if ($this->editingSupplierId) {
            $supplier = Supplier::findOrFail($this->editingSupplierId);
            $this->authorize('update', $supplier);
            $supplier->update($validated);
        } else {
            $this->authorize('create', Supplier::class);
            $supplier = Supplier::create([
                ...$validated,
                'user_id' => Auth::id(),
                'outstanding_balance' => 0.00,
            ]);

            if ($initialBalance > 0) {
                SupplierPayment::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => Auth::id(),
                    'date' => now()->toDateString(),
                    'type' => 'raw_material_purchase',
                    'amount' => $initialBalance,
                    'payment_method' => 'cash',
                    'notes' => __('Opening balance / raw material purchase'),
                ]);
            }
        }

        $this->showSupplierModal = false;
        unset($this->suppliers);
        Flux::toast(variant: 'success', text: __('Supplier saved successfully.'));
    }

    public function openPaymentModal(int $supplierId, string $defaultType = 'raw_material_purchase'): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $this->authorize('update', $supplier);

        $this->paymentSupplierId = $supplier->id;
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
        $supplier = Supplier::findOrFail($this->paymentSupplierId);
        $this->authorize('update', $supplier);

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
            'supplier_id' => $supplier->id,
            'user_id' => Auth::id(),
            'date' => $validated['payment_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['payment_notes'],
            'bill_path' => $billPath,
        ]);

        $this->showPaymentModal = false;
        $this->bill_image = null;
        unset($this->suppliers);
        unset($this->selectedLedgerSupplier);
        Flux::toast(variant: 'success', text: __('Supplier entry recorded successfully.'));
    }

    public function openLedgerModal(int $supplierId): void
    {
        $this->ledgerSupplierId = $supplierId;
        $this->showLedgerModal = true;
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = SupplierPayment::findOrFail($paymentId);
        $this->authorize('update', $payment->supplier);

        $payment->delete();
        unset($this->suppliers);
        unset($this->selectedLedgerSupplier);
        Flux::toast(variant: 'success', text: __('Entry deleted.'));
    }

    public function deleteSupplier(int $supplierId): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $this->authorize('delete', $supplier);

        $supplier->delete();
        unset($this->suppliers);
        Flux::toast(variant: 'success', text: __('Supplier deleted successfully.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Raw Material Suppliers') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage raw material vendors (e.g. Raja Assam for Bans, Suraj for Fatta), purchases, and balance ledgers.') }}</flux:text>
        </div>

        @can('create', App\Models\Supplier::class)
        <flux:button variant="primary" icon="plus" wire:click="createSupplier">{{ __('Add Supplier') }}</flux:button>
        @endcan
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Purchases (This Month)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->purchasesThisMonth, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Paid to Suppliers (This Month)') }}</flux:text>
            <flux:heading size="lg" class="text-green-600 dark:text-green-400">₹{{ number_format($this->paymentsThisMonth, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Balance Owed to Suppliers') }}</flux:text>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">₹{{ number_format($this->totalOutstandingBalance, 2) }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search supplier name, location, or material...')" icon="magnifying-glass" />
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->suppliers">
            <flux:table.columns>
                <flux:table.column>{{ __('Supplier Name') }}</flux:table.column>
                <flux:table.column>{{ __('Phone') }}</flux:table.column>
                <flux:table.column>{{ __('Location') }}</flux:table.column>
                <flux:table.column>{{ __('Material Supplied') }}</flux:table.column>
                <flux:table.column>{{ __('Total Purchased') }}</flux:table.column>
                <flux:table.column>{{ __('Total Paid') }}</flux:table.column>
                <flux:table.column>{{ __('Balance Owed') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Quick Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->suppliers as $supplier)
                <flux:table.row wire:key="supplier-{{ $supplier->id }}">
                    <flux:table.cell class="font-medium">
                        <button type="button" wire:click="openLedgerModal({{ $supplier->id }})" class="hover:underline text-indigo-600 dark:text-indigo-400 font-semibold text-start">
                            {{ $supplier->name }}
                        </button>
                    </flux:table.cell>
                    <flux:table.cell>{{ $supplier->phone ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $supplier->location ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="blue" size="sm">{{ $supplier->material_supplied ?? __('Raw Material') }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="font-semibold text-orange-600 dark:text-orange-400">
                        ₹{{ number_format((float) $supplier->payments->where('type', 'raw_material_purchase')->sum('amount'), 2) }}
                    </flux:table.cell>
                    <flux:table.cell class="font-semibold text-green-600 dark:text-green-400">
                        ₹{{ number_format((float) $supplier->payments->where('type', 'payment_made')->sum('amount'), 2) }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ((float) $supplier->outstanding_balance > 0)
                            <flux:badge color="red" size="sm">₹{{ number_format((float) $supplier->outstanding_balance, 2) }} due</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">₹0.00</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$supplier->status === 'active' ? 'green' : 'zinc'" size="sm">
                            {{ ucfirst($supplier->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button size="sm" variant="subtle" icon="shopping-bag" wire:click="openPaymentModal({{ $supplier->id }}, 'raw_material_purchase')">
                                {{ __('Purchase') }}
                            </flux:button>
                            <flux:button size="sm" variant="subtle" icon="banknotes" wire:click="openPaymentModal({{ $supplier->id }}, 'payment_made')">
                                {{ __('Pay') }}
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="document-text" wire:click="openLedgerModal({{ $supplier->id }})" title="{{ __('Ledger History') }}" />
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editSupplier({{ $supplier->id }})" />
                            @can('delete', $supplier)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteSupplier({{ $supplier->id }})" wire:confirm="{{ __('Delete this supplier and all their records?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="9" class="text-center text-zinc-500 py-6">{{ __('No suppliers registered yet. Click "Add Supplier" to create one.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Add/Edit Supplier Modal -->
    <flux:modal wire:model.self="showSupplierModal" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingSupplierId ? __('Edit Supplier') : __('Add New Supplier') }}</flux:heading>

            <form wire:submit="saveSupplier" class="flex flex-col gap-4">
                <flux:input wire:model="name" :label="__('Supplier Name')" placeholder="e.g. Raja Assam" required />
                <flux:input wire:model="phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />
                <flux:input wire:model="location" :label="__('Location / City')" placeholder="e.g. Assam" />
                <flux:input wire:model="material_supplied" :label="__('Material Supplied')" placeholder="e.g. Bans (Bamboo), Fatta" />

                @if (! $editingSupplierId)
                <flux:input type="number" step="0.01" min="0" wire:model="initial_balance" :label="__('Initial Opening Balance Owed (₹)')" placeholder="e.g. 40000 (optional)" />
                @endif

                <flux:select wire:model="status" :label="__('Status')">
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="notes" :label="__('Notes / Remarks')" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showSupplierModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Supplier') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

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

    <!-- Ledger History Modal -->
    <flux:modal wire:model.self="showLedgerModal" class="md:w-[720px]">
        @if ($this->selectedLedgerSupplier)
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200 dark:border-zinc-700 pb-3">
                <div>
                    <flux:heading size="lg">{{ $this->selectedLedgerSupplier->name }} – Ledger History</flux:heading>
                    <flux:text class="text-sm">Material: {{ $this->selectedLedgerSupplier->material_supplied ?? 'Raw Material' }} ({{ $this->selectedLedgerSupplier->location ?? 'Supplier' }})</flux:text>
                </div>
                <div class="flex items-center gap-2">
                    <flux:select wire:model.live="ledgerPeriod" class="w-36" size="sm">
                        <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
                        <flux:select.option value="this_week">{{ __('This Week') }}</flux:select.option>
                        <flux:select.option value="this_year">{{ __('This Year') }}</flux:select.option>
                        <flux:select.option value="all">{{ __('All Time') }}</flux:select.option>
                    </flux:select>

                    <flux:badge color="red" size="md">Balance Owed: ₹{{ number_format((float) $this->selectedLedgerSupplier->outstanding_balance, 2) }}</flux:badge>
                </div>
            </div>

            <!-- Ledger Period Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 text-xs">
                <div>
                    <span class="text-zinc-500 block">{{ __('Total Purchased in Period') }}</span>
                    <span class="font-bold text-sm text-orange-600 dark:text-orange-400">
                        ₹{{ number_format((float) $this->selectedLedgerSupplier->payments->where('type', 'raw_material_purchase')->sum('amount'), 2) }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 block">{{ __('Total Paid in Period') }}</span>
                    <span class="font-bold text-sm text-green-600 dark:text-green-400">
                        ₹{{ number_format((float) $this->selectedLedgerSupplier->payments->where('type', 'payment_made')->sum('amount'), 2) }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 block">{{ __('Total Entries') }}</span>
                    <span class="font-bold text-sm text-zinc-800 dark:text-zinc-200">
                        {{ $this->selectedLedgerSupplier->payments->count() }}
                    </span>
                </div>
            </div>

            <div class="w-full max-h-96 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-start text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="p-3 text-start">{{ __('Date') }}</th>
                            <th class="p-3 text-start">{{ __('Type') }}</th>
                            <th class="p-3 text-start">{{ __('Amount') }}</th>
                            <th class="p-3 text-start">{{ __('Method') }}</th>
                            <th class="p-3 text-start">{{ __('Notes') }}</th>
                            <th class="p-3 text-start">{{ __('Bill / Receipt') }}</th>
                            <th class="p-3 text-end"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->selectedLedgerSupplier->payments as $payment)
                        <tr>
                            <td class="p-3 font-medium">{{ $payment->date->format('d M Y') }}</td>
                            <td class="p-3">
                                @if ($payment->type === 'raw_material_purchase')
                                    <flux:badge color="orange" size="sm">Purchase Invoice</flux:badge>
                                @else
                                    <flux:badge color="green" size="sm">Payment Made</flux:badge>
                                @endif
                            </td>
                            <td class="p-3 font-semibold">₹{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="p-3 uppercase text-xs">{{ $payment->payment_method }}</td>
                            <td class="p-3 text-zinc-500 text-xs">{{ $payment->notes ?? '-' }}</td>
                            <td class="p-3 text-xs">
                                @if ($payment->bill_path)
                                    <a href="{{ Storage::url($payment->bill_path) }}" target="_blank" class="inline-flex items-center gap-1 font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        📄 {{ __('View Bill') }}
                                    </a>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-end">
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deletePayment({{ $payment->id }})" wire:confirm="{{ __('Delete this entry?') }}" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-zinc-500">{{ __('No transactions found for this supplier.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center">
                <flux:button variant="subtle" icon="plus" wire:click="openPaymentModal({{ $this->selectedLedgerSupplier->id }}, 'raw_material_purchase')">
                    {{ __('Record Purchase') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="$set('showLedgerModal', false)">{{ __('Close') }}</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>

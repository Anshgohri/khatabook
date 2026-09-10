<?php

use App\Models\Financier;
use App\Models\FinancierPayment;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Financiers')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $payoutType = '';

    // Financier Modal State
    public bool $showFinancierModal = false;
    public ?int $editingFinancierId = null;
    public string $name = '';
    public string $phone = '';
    public string $payout_type = 'daily'; // daily, weekly, monthly
    public float $default_payment_amount = 0.0;
    public float $initial_loan_amount = 0.0;
    public string $status = 'active';
    public string $notes = '';

    // Payment Modal State
    public bool $showPaymentModal = false;
    public ?int $paymentFinancierId = null;
    public string $type = 'daily_payment'; // daily_payment, weekly_payment, monthly_payment, loan_received, loan_repaid, interest_payment
    public float $amount = 0.0;
    public string $payment_method = 'cash';
    public string $payment_date = '';
    public string $payment_notes = '';

    // Ledger Modal State
    public bool $showLedgerModal = false;
    public ?int $ledgerFinancierId = null;
    public string $ledgerPeriod = 'this_month'; // this_month, this_week, this_year, all

    public function mount(): void
    {
        $this->authorize('viewAny', Financier::class);
        $this->payment_date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPayoutType(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function paymentsThisWeek(): float
    {
        $user = Auth::user();
        return (float) FinancierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');
    }

    #[Computed]
    public function paymentsThisMonth(): float
    {
        $user = Auth::user();
        return (float) FinancierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    }

    #[Computed]
    public function paymentsThisYear(): float
    {
        $user = Auth::user();
        return (float) FinancierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
            ->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])
            ->sum('amount');
    }

    #[Computed]
    public function totalOutstandingBalance(): float
    {
        $user = Auth::user();
        return (float) Financier::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->sum('outstanding_balance');
    }

    #[Computed]
    public function financiers()
    {
        $user = Auth::user();

        return Financier::query()
            ->with(['payments'])
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->when($this->payoutType, fn ($query) => $query->where('payout_type', $this->payoutType))
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function selectedLedgerFinancier()
    {
        if (! $this->ledgerFinancierId) {
            return null;
        }

        return Financier::with(['payments' => function ($query) {
            $query->when($this->ledgerPeriod === 'this_week', fn ($q) => $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]))
                ->when($this->ledgerPeriod === 'this_month', fn ($q) => $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]))
                ->when($this->ledgerPeriod === 'this_year', fn ($q) => $q->whereBetween('date', [now()->startOfYear(), now()->endOfYear()]))
                ->latest('date');
        }])->find($this->ledgerFinancierId);
    }

    public function createFinancier(): void
    {
        $this->authorize('create', Financier::class);

        $this->reset(['editingFinancierId', 'name', 'phone', 'notes', 'initial_loan_amount']);
        $this->payout_type = 'daily';
        $this->default_payment_amount = 500.0;
        $this->status = 'active';
        $this->showFinancierModal = true;
    }

    public function editFinancier(int $id): void
    {
        $financier = Financier::findOrFail($id);
        $this->authorize('update', $financier);

        $this->editingFinancierId = $financier->id;
        $this->name = $financier->name;
        $this->phone = (string) $financier->phone;
        $this->payout_type = $financier->payout_type;
        $this->default_payment_amount = (float) $financier->default_payment_amount;
        $this->initial_loan_amount = 0.0;
        $this->status = $financier->status;
        $this->notes = (string) $financier->notes;
        $this->showFinancierModal = true;
    }

    public function saveFinancier(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'payout_type' => ['required', 'in:daily,weekly,monthly'],
            'default_payment_amount' => ['required', 'numeric', 'min:0'],
            'initial_loan_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $initialLoan = (float) ($validated['initial_loan_amount'] ?? 0.0);
        unset($validated['initial_loan_amount']);

        if ($this->editingFinancierId) {
            $financier = Financier::findOrFail($this->editingFinancierId);
            $this->authorize('update', $financier);
            $financier->update($validated);
        } else {
            $this->authorize('create', Financier::class);
            $financier = Financier::create([
                ...$validated,
                'user_id' => Auth::id(),
                'outstanding_balance' => 0.00,
            ]);

            if ($initialLoan > 0) {
                FinancierPayment::create([
                    'financier_id' => $financier->id,
                    'user_id' => Auth::id(),
                    'date' => now()->toDateString(),
                    'type' => 'loan_received',
                    'amount' => $initialLoan,
                    'payment_method' => 'cash',
                    'notes' => __('Initial loan amount / opening balance'),
                ]);
            }
        }

        $this->showFinancierModal = false;
        unset($this->financiers);
        Flux::toast(variant: 'success', text: __('Financier saved successfully.'));
    }

    public function openPaymentModal(int $financierId, ?string $defaultType = null): void
    {
        $financier = Financier::findOrFail($financierId);
        $this->authorize('update', $financier);

        $this->paymentFinancierId = $financier->id;
        $this->type = $defaultType ?? match ($financier->payout_type) {
            'monthly' => 'monthly_payment',
            'weekly' => 'weekly_payment',
            default => 'daily_payment',
        };
        $this->amount = (float) $financier->default_payment_amount;
        $this->payment_method = 'cash';
        $this->payment_date = now()->toDateString();
        $this->payment_notes = '';
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $financier = Financier::findOrFail($this->paymentFinancierId);
        $this->authorize('update', $financier);

        $validated = $this->validate([
            'type' => ['required', 'in:daily_payment,weekly_payment,monthly_payment,loan_received,loan_repaid,interest_payment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,bank_transfer,cheque,other'],
            'payment_date' => ['required', 'date'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        FinancierPayment::create([
            'financier_id' => $financier->id,
            'user_id' => Auth::id(),
            'date' => $validated['payment_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['payment_notes'],
        ]);

        $this->showPaymentModal = false;
        unset($this->financiers);
        unset($this->selectedLedgerFinancier);
        Flux::toast(variant: 'success', text: __('Financier payment recorded successfully.'));
    }

    public function openLedgerModal(int $financierId): void
    {
        $this->ledgerFinancierId = $financierId;
        $this->showLedgerModal = true;
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = FinancierPayment::findOrFail($paymentId);
        $this->authorize('update', $payment->financier);

        $payment->delete();
        unset($this->financiers);
        unset($this->selectedLedgerFinancier);
        Flux::toast(variant: 'success', text: __('Payment entry deleted.'));
    }

    public function deleteFinancier(int $financierId): void
    {
        $financier = Financier::findOrFail($financierId);
        $this->authorize('delete', $financier);

        $financier->delete();
        unset($this->financiers);
        Flux::toast(variant: 'success', text: __('Financier deleted successfully.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Financiers Ledger') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage daily & monthly financier payouts, loan amounts, and repayments.') }}</flux:text>
        </div>

        @can('create', App\Models\Financier::class)
        <flux:button variant="primary" icon="plus" wire:click="createFinancier">{{ __('Add Financier') }}</flux:button>
        @endcan
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Paid to Financiers (This Week)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->paymentsThisWeek, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Paid to Financiers (This Month)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->paymentsThisMonth, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Paid to Financiers (This Year)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->paymentsThisYear, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Outstanding Loan Balance') }}</flux:text>
            <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($this->totalOutstandingBalance, 2) }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search financier name or phone...')" icon="magnifying-glass" />
        
        <flux:select wire:model.live="payoutType" :placeholder="__('All Payout Types')">
            <flux:select.option value="">{{ __('All Payout Types') }}</flux:select.option>
            <flux:select.option value="daily">{{ __('Daily Paid') }}</flux:select.option>
            <flux:select.option value="weekly">{{ __('Weekly Paid') }}</flux:select.option>
            <flux:select.option value="monthly">{{ __('Monthly Paid') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->financiers">
            <flux:table.columns>
                <flux:table.column>{{ __('Financier Name') }}</flux:table.column>
                <flux:table.column>{{ __('Phone') }}</flux:table.column>
                <flux:table.column>{{ __('Payout Type') }}</flux:table.column>
                <flux:table.column>{{ __('Installment Rate') }}</flux:table.column>
                <flux:table.column>{{ __('Total Loan Taken') }}</flux:table.column>
                <flux:table.column>{{ __('Total Paid') }}</flux:table.column>
                <flux:table.column>{{ __('Remaining Balance') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Quick Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->financiers as $financier)
                <flux:table.row wire:key="financier-{{ $financier->id }}">
                    <flux:table.cell class="font-medium">
                        <button type="button" wire:click="openLedgerModal({{ $financier->id }})" class="hover:underline text-indigo-600 dark:text-indigo-400 font-semibold text-start">
                            {{ $financier->name }}
                        </button>
                    </flux:table.cell>
                    <flux:table.cell>{{ $financier->phone ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$financier->payout_type === 'daily' ? 'blue' : ($financier->payout_type === 'weekly' ? 'emerald' : 'purple')" size="sm">
                            {{ ucfirst($financier->payout_type) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>₹{{ number_format((float) $financier->default_payment_amount, 2) }} / {{ $financier->payout_type }}</flux:table.cell>
                    <flux:table.cell class="font-semibold text-orange-600 dark:text-orange-400">
                        ₹{{ number_format($financier->total_loan_received, 2) }}
                    </flux:table.cell>
                    <flux:table.cell class="font-semibold text-green-600 dark:text-green-400">
                        ₹{{ number_format($financier->total_paid, 2) }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ((float) $financier->outstanding_balance > 0)
                            <flux:badge color="orange" size="sm">₹{{ number_format((float) $financier->outstanding_balance, 2) }} balance</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">₹0.00</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$financier->status === 'active' ? 'green' : 'zinc'" size="sm">
                            {{ ucfirst($financier->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button size="sm" variant="subtle" icon="banknotes" wire:click="openPaymentModal({{ $financier->id }})">
                                {{ __('Pay Now') }}
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="document-text" wire:click="openLedgerModal({{ $financier->id }})" title="{{ __('Ledger History') }}" />
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editFinancier({{ $financier->id }})" />
                            @can('delete', $financier)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteFinancier({{ $financier->id }})" wire:confirm="{{ __('Delete this financier and all their payment records?') }}" title="{{ __('Delete Financier') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="9" class="text-center text-zinc-500 py-6">{{ __('No financiers registered yet. Click "Add Financier" to create one.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Add/Edit Financier Modal -->
    <flux:modal wire:model.self="showFinancierModal" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingFinancierId ? __('Edit Financier') : __('Add New Financier') }}</flux:heading>

            <form wire:submit="saveFinancier" class="flex flex-col gap-4">
                <flux:input wire:model="name" :label="__('Financier / Company Name')" placeholder="e.g. Mahavir Finance" required />
                <flux:input wire:model="phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />
                
                <flux:select wire:model="payout_type" :label="__('Payout Frequency')" required>
                    <flux:select.option value="daily">{{ __('Daily Paid') }}</flux:select.option>
                    <flux:select.option value="weekly">{{ __('Weekly Paid') }}</flux:select.option>
                    <flux:select.option value="monthly">{{ __('Monthly Paid') }}</flux:select.option>
                </flux:select>

                <flux:input type="number" step="0.01" min="0" wire:model="default_payment_amount" :label="__('Default Payment Amount (₹)')" placeholder="e.g. 500 or 5000" required />
                
                @if (! $editingFinancierId)
                <flux:input type="number" step="0.01" min="0" wire:model="initial_loan_amount" :label="__('Initial Loan Amount / Opening Balance (₹)')" placeholder="e.g. 100000 (optional)" />
                @endif
                
                <flux:select wire:model="status" :label="__('Status')">
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="notes" :label="__('Notes / Remarks')" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showFinancierModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Financier') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Record Payment / Loan Modal -->
    <flux:modal wire:model.self="showPaymentModal" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Log Financier Payment / Loan') }}</flux:heading>

            <form wire:submit="savePayment" class="flex flex-col gap-4">
                <flux:select wire:model="type" :label="__('Transaction Type')" required>
                    <flux:select.option value="daily_payment">{{ __('Daily Installment Payment') }}</flux:select.option>
                    <flux:select.option value="weekly_payment">{{ __('Weekly Installment Payment') }}</flux:select.option>
                    <flux:select.option value="monthly_payment">{{ __('Monthly Installment Payment') }}</flux:select.option>
                    <flux:select.option value="loan_received">{{ __('Loan Received from Financier (Increases Balance)') }}</flux:select.option>
                    <flux:select.option value="loan_repaid">{{ __('Principal Loan Repaid') }}</flux:select.option>
                    <flux:select.option value="interest_payment">{{ __('Interest Paid') }}</flux:select.option>
                </flux:select>

                <flux:input type="number" step="0.01" min="0.01" wire:model="amount" :label="__('Amount (₹)')" placeholder="e.g. 500 or 50000" required />
                <flux:input type="date" wire:model="payment_date" :label="__('Date')" required />

                <flux:select wire:model="payment_method" :label="__('Payment Method')">
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="upi">{{ __('UPI / PhonePe / GPay') }}</flux:select.option>
                    <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                    <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="payment_notes" :label="__('Notes / Reason')" placeholder="e.g. Daily installment paid" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showPaymentModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Payment') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Ledger History Modal -->
    <flux:modal wire:model.self="showLedgerModal" class="md:w-[650px]">
        @if ($this->selectedLedgerFinancier)
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200 dark:border-zinc-700 pb-3">
                <div>
                    <flux:heading size="lg">{{ $this->selectedLedgerFinancier->name }} – Ledger</flux:heading>
                    <flux:text class="text-sm">Type: {{ ucfirst($this->selectedLedgerFinancier->payout_type) }} (₹{{ number_format((float) $this->selectedLedgerFinancier->default_payment_amount, 2) }})</flux:text>
                </div>
                <div class="flex items-center gap-2">
                    <flux:select wire:model.live="ledgerPeriod" class="w-36" size="sm">
                        <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
                        <flux:select.option value="this_week">{{ __('This Week') }}</flux:select.option>
                        <flux:select.option value="this_year">{{ __('This Year') }}</flux:select.option>
                        <flux:select.option value="all">{{ __('All Time') }}</flux:select.option>
                    </flux:select>

                    <flux:badge color="orange" size="md">Balance: ₹{{ number_format((float) $this->selectedLedgerFinancier->outstanding_balance, 2) }}</flux:badge>
                </div>
            </div>

            <!-- Ledger Period Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 text-xs">
                <div>
                    <span class="text-zinc-500 block">{{ __('Total Paid in Period') }}</span>
                    <span class="font-bold text-sm text-green-600 dark:text-green-400">
                        ₹{{ number_format((float) $this->selectedLedgerFinancier->payments->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])->sum('amount'), 2) }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 block">{{ __('Loans Received in Period') }}</span>
                    <span class="font-bold text-sm text-orange-600 dark:text-orange-400">
                        ₹{{ number_format((float) $this->selectedLedgerFinancier->payments->where('type', 'loan_received')->sum('amount'), 2) }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 block">{{ __('Total Entries') }}</span>
                    <span class="font-bold text-sm text-zinc-800 dark:text-zinc-200">
                        {{ $this->selectedLedgerFinancier->payments->count() }}
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
                            <th class="p-3 text-end"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->selectedLedgerFinancier->payments as $payment)
                        <tr>
                            <td class="p-3 font-medium">{{ $payment->date->format('d M Y') }}</td>
                            <td class="p-3">
                                @if ($payment->type === 'loan_received')
                                    <flux:badge color="orange" size="sm">Loan Received</flux:badge>
                                @elseif (in_array($payment->type, ['daily_payment', 'weekly_payment', 'monthly_payment']))
                                    <flux:badge color="blue" size="sm">{{ ucfirst(str_replace('_', ' ', $payment->type)) }}</flux:badge>
                                @elseif ($payment->type === 'loan_repaid')
                                    <flux:badge color="green" size="sm">Loan Repaid</flux:badge>
                                @else
                                    <flux:badge color="purple" size="sm">Interest Paid</flux:badge>
                                @endif
                            </td>
                            <td class="p-3 font-semibold">₹{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="p-3 uppercase text-xs">{{ $payment->payment_method }}</td>
                            <td class="p-3 text-zinc-500 text-xs">{{ $payment->notes ?? '-' }}</td>
                            <td class="p-3 text-end">
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deletePayment({{ $payment->id }})" wire:confirm="{{ __('Delete this payment entry?') }}" />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-zinc-500">{{ __('No payment records found for this financier.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center">
                <flux:button variant="subtle" icon="plus" wire:click="openPaymentModal({{ $this->selectedLedgerFinancier->id }})">
                    {{ __('Record Payment') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="$set('showLedgerModal', false)">{{ __('Close') }}</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>

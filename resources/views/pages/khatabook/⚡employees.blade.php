<?php

use App\Models\Employee;
use App\Models\EmployeePayment;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Employees')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    // Employee Modal State
    public bool $showEmployeeModal = false;
    public ?int $editingEmployeeId = null;
    public string $name = '';
    public string $phone = '';
    public float $default_daily_rate = 0.0;
    public string $status = 'active';
    public string $notes = '';

    // Payment Modal State
    public bool $showPaymentModal = false;
    public ?int $paymentEmployeeId = null;
    public string $type = 'daily_pay'; // daily_pay, advance_given, advance_repaid, salary_deduction
    public float $amount = 0.0;
    public string $payment_method = 'cash';
    public string $payment_date = '';
    public string $payment_notes = '';

    // Ledger Modal State
    public bool $showLedgerModal = false;
    public ?int $ledgerEmployeeId = null;
    public string $ledgerPeriod = 'this_month'; // this_month, this_week, this_year, all

    public function mount(): void
    {
        $this->authorize('viewAny', Employee::class);
        $this->payment_date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function wagesThisWeek(): float
    {
        $user = Auth::user();
        return (float) EmployeePayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'daily_pay')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');
    }

    #[Computed]
    public function wagesThisMonth(): float
    {
        $user = Auth::user();
        return (float) EmployeePayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'daily_pay')
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    }

    #[Computed]
    public function wagesThisYear(): float
    {
        $user = Auth::user();
        return (float) EmployeePayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'daily_pay')
            ->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])
            ->sum('amount');
    }

    #[Computed]
    public function totalAdvanceOutstanding(): float
    {
        $user = Auth::user();
        return (float) Employee::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->sum('advance_balance');
    }

    #[Computed]
    public function employees()
    {
        $user = Auth::user();

        return Employee::query()
            ->with(['payments'])
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function selectedLedgerEmployee()
    {
        if (! $this->ledgerEmployeeId) {
            return null;
        }

        return Employee::with(['payments' => function ($query) {
            $query->when($this->ledgerPeriod === 'this_week', fn ($q) => $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]))
                ->when($this->ledgerPeriod === 'this_month', fn ($q) => $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]))
                ->when($this->ledgerPeriod === 'this_year', fn ($q) => $q->whereBetween('date', [now()->startOfYear(), now()->endOfYear()]))
                ->latest('date');
        }])->find($this->ledgerEmployeeId);
    }

    public function createEmployee(): void
    {
        $this->authorize('create', Employee::class);

        $this->reset(['editingEmployeeId', 'name', 'phone', 'notes']);
        $this->default_daily_rate = 800.0;
        $this->status = 'active';
        $this->showEmployeeModal = true;
    }

    public function editEmployee(int $id): void
    {
        $employee = Employee::findOrFail($id);
        $this->authorize('update', $employee);

        $this->editingEmployeeId = $employee->id;
        $this->name = $employee->name;
        $this->phone = (string) $employee->phone;
        $this->default_daily_rate = (float) $employee->default_daily_rate;
        $this->status = $employee->status;
        $this->notes = (string) $employee->notes;
        $this->showEmployeeModal = true;
    }

    public function saveEmployee(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'default_daily_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->editingEmployeeId) {
            $employee = Employee::findOrFail($this->editingEmployeeId);
            $this->authorize('update', $employee);
            $employee->update($validated);
        } else {
            $this->authorize('create', Employee::class);
            Employee::create([
                ...$validated,
                'user_id' => Auth::id(),
                'advance_balance' => 0.00,
            ]);
        }

        $this->showEmployeeModal = false;
        unset($this->employees);
        Flux::toast(variant: 'success', text: __('Employee saved successfully.'));
    }

    public function openPaymentModal(int $employeeId, string $defaultType = 'daily_pay'): void
    {
        $employee = Employee::findOrFail($employeeId);
        $this->authorize('update', $employee);

        $this->paymentEmployeeId = $employee->id;
        $this->type = $defaultType;
        $this->amount = $defaultType === 'daily_pay' ? (float) $employee->default_daily_rate : 0.0;
        $this->payment_method = 'cash';
        $this->payment_date = now()->toDateString();
        $this->payment_notes = '';
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $employee = Employee::findOrFail($this->paymentEmployeeId);
        $this->authorize('update', $employee);

        $validated = $this->validate([
            'type' => ['required', 'in:daily_pay,advance_given,advance_repaid,salary_deduction'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,bank_transfer,cheque,other'],
            'payment_date' => ['required', 'date'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        EmployeePayment::create([
            'employee_id' => $employee->id,
            'user_id' => Auth::id(),
            'date' => $validated['payment_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['payment_notes'],
        ]);

        $this->showPaymentModal = false;
        unset($this->employees);
        unset($this->selectedLedgerEmployee);
        Flux::toast(variant: 'success', text: __('Payment recorded successfully.'));
    }

    public function openLedgerModal(int $employeeId): void
    {
        $this->ledgerEmployeeId = $employeeId;
        $this->showLedgerModal = true;
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = EmployeePayment::findOrFail($paymentId);
        $this->authorize('update', $payment->employee);

        $payment->delete();
        unset($this->employees);
        unset($this->selectedLedgerEmployee);
        Flux::toast(variant: 'success', text: __('Payment entry deleted.'));
    }

    public function deleteEmployee(int $employeeId): void
    {
        $employee = Employee::findOrFail($employeeId);
        $this->authorize('delete', $employee);

        $employee->delete();
        unset($this->employees);
        Flux::toast(variant: 'success', text: __('Employee deleted successfully.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Employee Ledger & Daily Pay') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Track daily pay (₹500 / ₹800), advance amounts given (e.g. ₹1,00,000), and repayments.') }}</flux:text>
        </div>

        @can('create', App\Models\Employee::class)
        <flux:button variant="primary" icon="plus" wire:click="createEmployee">{{ __('Add Employee') }}</flux:button>
        @endcan
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Wages Paid (This Week)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->wagesThisWeek, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Wages Paid (This Month)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->wagesThisMonth, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Wages Paid (This Year)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->wagesThisYear, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Advances Outstanding') }}</flux:text>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">₹{{ number_format($this->totalAdvanceOutstanding, 2) }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search employee name or phone...')" icon="magnifying-glass" />
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->employees">
            <flux:table.columns>
                <flux:table.column>{{ __('Employee Name') }}</flux:table.column>
                <flux:table.column>{{ __('Phone') }}</flux:table.column>
                <flux:table.column>{{ __('Default Daily Pay') }}</flux:table.column>
                <flux:table.column>{{ __('Advance Balance Owed') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Quick Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->employees as $employee)
                <flux:table.row wire:key="employee-{{ $employee->id }}">
                    <flux:table.cell class="font-medium">
                        <button type="button" wire:click="openLedgerModal({{ $employee->id }})" class="hover:underline text-indigo-600 dark:text-indigo-400 font-semibold text-start">
                            {{ $employee->name }}
                        </button>
                    </flux:table.cell>
                    <flux:table.cell>{{ $employee->phone ?? '-' }}</flux:table.cell>
                    <flux:table.cell>₹{{ number_format((float) $employee->default_daily_rate, 2) }} / day</flux:table.cell>
                    <flux:table.cell>
                        @if ((float) $employee->advance_balance > 0)
                            <flux:badge color="red" size="sm">₹{{ number_format((float) $employee->advance_balance, 2) }} advance due</flux:badge>
                        @elseif ((float) $employee->advance_balance < 0)
                            <flux:badge color="green" size="sm">₹{{ number_format(abs((float) $employee->advance_balance), 2) }} credit</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">₹0.00</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$employee->status === 'active' ? 'green' : 'zinc'" size="sm">
                            {{ ucfirst($employee->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:button size="sm" variant="subtle" icon="banknotes" wire:click="openPaymentModal({{ $employee->id }}, 'daily_pay')">
                                {{ __('Daily Pay') }}
                            </flux:button>
                            <flux:button size="sm" variant="subtle" icon="arrow-up-circle" wire:click="openPaymentModal({{ $employee->id }}, 'advance_given')">
                                {{ __('Give Advance') }}
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="document-text" wire:click="openLedgerModal({{ $employee->id }})" title="{{ __('Ledger History') }}" />
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editEmployee({{ $employee->id }})" />
                            @can('delete', $employee)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteEmployee({{ $employee->id }})" wire:confirm="{{ __('Delete this employee and all their records?') }}" title="{{ __('Delete Employee') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-500 py-6">{{ __('No employees registered yet. Click "Add Employee" to create one.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Add/Edit Employee Modal -->
    <flux:modal wire:model.self="showEmployeeModal" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingEmployeeId ? __('Edit Employee') : __('Add New Employee') }}</flux:heading>

            <form wire:submit="saveEmployee" class="flex flex-col gap-4">
                <flux:input wire:model="name" :label="__('Employee Name')" placeholder="e.g. Ramesh Kumar" required />
                <flux:input wire:model="phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />
                <flux:input type="number" step="1" min="0" wire:model="default_daily_rate" :label="__('Default Daily Wage (₹)')" placeholder="e.g. 800 or 500" required />
                
                <flux:select wire:model="status" :label="__('Status')">
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="notes" :label="__('Notes / Remarks')" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showEmployeeModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Employee') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Record Payment / Advance Modal -->
    <flux:modal wire:model.self="showPaymentModal" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Log Payment / Advance') }}</flux:heading>

            <form wire:submit="savePayment" class="flex flex-col gap-4">
                <flux:select wire:model="type" :label="__('Transaction Type')" required>
                    <flux:select.option value="daily_pay">{{ __('Daily Wage Pay (₹500 / ₹800)') }}</flux:select.option>
                    <flux:select.option value="advance_given">{{ __('Give Advance Money (Increases Advance Balance)') }}</flux:select.option>
                    <flux:select.option value="advance_repaid">{{ __('Advance Cash Repaid by Employee') }}</flux:select.option>
                    <flux:select.option value="salary_deduction">{{ __('Adjust Advance Against Daily Wage') }}</flux:select.option>
                </flux:select>

                <flux:input type="number" step="0.01" min="0.01" wire:model="amount" :label="__('Amount (₹)')" placeholder="e.g. 800 or 100000" required />
                <flux:input type="date" wire:model="payment_date" :label="__('Date')" required />

                <flux:select wire:model="payment_method" :label="__('Payment Method')">
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="upi">{{ __('UPI / PhonePe / GPay') }}</flux:select.option>
                    <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                    <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="payment_notes" :label="__('Notes / Reason')" placeholder="e.g. Paid 1 Lakh initial advance for emergency" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showPaymentModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Payment') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Ledger History Modal -->
    <flux:modal wire:model.self="showLedgerModal" class="md:w-[600px]">
        @if ($this->selectedLedgerEmployee)
        <div class="flex flex-col gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-zinc-200 dark:border-zinc-700 pb-3">
                <div>
                    <flux:heading size="lg">{{ $this->selectedLedgerEmployee->name }} – Ledger History</flux:heading>
                    <flux:text class="text-sm">Rate: ₹{{ number_format((float) $this->selectedLedgerEmployee->default_daily_rate, 2) }}/day</flux:text>
                </div>
                <div class="flex items-center gap-2">
                    <flux:select wire:model.live="ledgerPeriod" class="w-36" size="sm">
                        <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
                        <flux:select.option value="this_week">{{ __('This Week') }}</flux:select.option>
                        <flux:select.option value="this_year">{{ __('This Year') }}</flux:select.option>
                        <flux:select.option value="all">{{ __('All Time') }}</flux:select.option>
                    </flux:select>

                    <flux:badge color="red" size="md">Advance Due: ₹{{ number_format((float) $this->selectedLedgerEmployee->advance_balance, 2) }}</flux:badge>
                </div>
            </div>

            <!-- Ledger Period Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 text-xs">
                <div>
                    <span class="text-zinc-500 block">{{ __('Wages Paid in Period') }}</span>
                    <span class="font-bold text-sm text-blue-600 dark:text-blue-400">
                        ₹{{ number_format((float) $this->selectedLedgerEmployee->payments->where('type', 'daily_pay')->sum('amount'), 2) }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 block">{{ __('Advance Given in Period') }}</span>
                    <span class="font-bold text-sm text-red-600 dark:text-red-400">
                        ₹{{ number_format((float) $this->selectedLedgerEmployee->payments->where('type', 'advance_given')->sum('amount'), 2) }}
                    </span>
                </div>
                <div>
                    <span class="text-zinc-500 block">{{ __('Advance Repaid/Deducted') }}</span>
                    <span class="font-bold text-sm text-green-600 dark:text-green-400">
                        ₹{{ number_format((float) $this->selectedLedgerEmployee->payments->whereIn('type', ['advance_repaid', 'salary_deduction'])->sum('amount'), 2) }}
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
                        @forelse ($this->selectedLedgerEmployee->payments as $payment)
                        <tr>
                            <td class="p-3 font-medium">{{ $payment->date->format('d M Y') }}</td>
                            <td class="p-3">
                                @if ($payment->type === 'advance_given')
                                    <flux:badge color="red" size="sm">Advance Given</flux:badge>
                                @elseif ($payment->type === 'daily_pay')
                                    <flux:badge color="blue" size="sm">Daily Pay</flux:badge>
                                @elseif ($payment->type === 'advance_repaid')
                                    <flux:badge color="green" size="sm">Advance Repaid</flux:badge>
                                @else
                                    <flux:badge color="purple" size="sm">Deduction</flux:badge>
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
                            <td colspan="6" class="p-4 text-center text-zinc-500">{{ __('No payment records found for this employee.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center">
                <flux:button variant="subtle" icon="plus" wire:click="openPaymentModal({{ $this->selectedLedgerEmployee->id }}, 'advance_given')">
                    {{ __('Record Advance') }}
                </flux:button>
                <flux:button variant="ghost" wire:click="$set('showLedgerModal', false)">{{ __('Close') }}</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>

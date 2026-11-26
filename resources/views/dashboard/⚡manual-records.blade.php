<?php

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Employee;
use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\ManualRecord;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Manual Records & Daily Dues')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $tab = 'all'; // all, today, overdue, pending, paid

    #[Url]
    public string $partyType = ''; // '', supplier, financier, employee, other

    #[Url]
    public string $frequencyFilter = ''; // '', daily, weekly, monthly, one_time

    #[Url]
    public string $datePreset = ''; // '', today, this_week, this_month, custom

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    // Create / Edit Modal State
    public bool $showRecordModal = false;
    public ?int $editingRecordId = null;
    public string $recordPartyType = 'supplier'; // supplier, financier, employee, other
    public ?int $selectedSupplierId = null;
    public ?int $selectedFinancierId = null;
    public ?int $selectedEmployeeId = null;
    public string $partyName = '';
    public string $partyPhone = '';
    public ?float $amount = null;
    public string $dueDate = '';
    public string $frequency = 'daily'; // daily, weekly, monthly, one_time
    public string $priority = 'normal'; // low, normal, high, urgent
    public string $notes = '';
    public bool $isRecurring = true;

    // Mark as Paid Modal State
    public bool $showPayModal = false;
    public ?int $payingRecordId = null;
    public ?float $payAmount = null;
    public string $payDate = '';
    public string $payMethod = 'cash'; // cash, upi, bank_transfer, cheque, other
    public ?int $payBankAccountId = null;
    public bool $advanceNext = true;
    public bool $syncWithEntityLedger = false;

    public function mount(): void
    {
        $this->authorize('viewAny', ManualRecord::class);
        $this->dueDate = Carbon::today()->toDateString();
        $this->payDate = Carbon::today()->toDateString();
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'tab', 'partyType', 'frequencyFilter', 'datePreset', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function updatedDatePreset(string $preset): void
    {
        match ($preset) {
            'today' => [
                $this->dateFrom = Carbon::today()->toDateString(),
                $this->dateTo = Carbon::today()->toDateString(),
            ],
            'this_week' => [
                $this->dateFrom = Carbon::now()->startOfWeek()->toDateString(),
                $this->dateTo = Carbon::now()->endOfWeek()->toDateString(),
            ],
            'this_month' => [
                $this->dateFrom = Carbon::now()->startOfMonth()->toDateString(),
                $this->dateTo = Carbon::now()->endOfMonth()->toDateString(),
            ],
            default => [
                $this->dateFrom = '',
                $this->dateTo = '',
            ],
        };
    }

    public function setPartyType(string $type): void
    {
        $this->recordPartyType = $type;
        $this->selectedSupplierId = null;
        $this->selectedFinancierId = null;
        $this->selectedEmployeeId = null;
        $this->partyName = '';
        $this->partyPhone = '';
    }

    public function updatedRecordPartyType(string $type): void
    {
        $this->setPartyType($type);
    }

    public function updatedSelectedSupplierId(mixed $id): void
    {
        $id = ! empty($id) ? (int) $id : null;
        $this->selectedSupplierId = $id;

        if (! $id) {
            return;
        }

        $supplier = Supplier::find($id);
        if ($supplier) {
            $this->partyName = $supplier->name;
            $this->partyPhone = (string) ($supplier->phone ?? '');
        }
    }

    public function updatedSelectedFinancierId(mixed $id): void
    {
        $id = ! empty($id) ? (int) $id : null;
        $this->selectedFinancierId = $id;

        if (! $id) {
            return;
        }

        $financier = Financier::find($id);
        if ($financier) {
            $this->partyName = $financier->name;
            $this->partyPhone = (string) ($financier->phone ?? '');
            if (! $this->amount && (float) $financier->default_payment_amount > 0) {
                $this->amount = (float) $financier->default_payment_amount;
            }
            if (! empty($financier->payout_type)) {
                $this->frequency = match ($financier->payout_type) {
                    'daily' => 'daily',
                    'weekly' => 'weekly',
                    'monthly' => 'monthly',
                    default => 'daily',
                };
            }
        }
    }

    public function updatedSelectedEmployeeId(mixed $id): void
    {
        $id = ! empty($id) ? (int) $id : null;
        $this->selectedEmployeeId = $id;

        if (! $id) {
            return;
        }

        $employee = Employee::find($id);
        if ($employee) {
            $this->partyName = $employee->name;
            $this->partyPhone = (string) ($employee->phone ?? '');
            if (! $this->amount && (float) $employee->default_daily_rate > 0) {
                $this->amount = (float) $employee->default_daily_rate;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Computed Properties & Query
    // ─────────────────────────────────────────────────────────────────────────

    #[Computed]
    public function suppliersList()
    {
        return Supplier::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'outstanding_balance']);
    }

    #[Computed]
    public function financiersList()
    {
        return Financier::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'outstanding_balance', 'default_payment_amount', 'payout_type', 'interest_type']);
    }

    #[Computed]
    public function employeesList()
    {
        return Employee::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone', 'default_daily_rate']);
    }

    #[Computed]
    public function bankAccountsList()
    {
        return BankAccount::query()->where('is_active', true)->orderBy('account_name')->get(['id', 'account_name', 'bank_name', 'account_number', 'current_balance']);
    }

    #[Computed]
    public function stats(): array
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $dueTodayQuery = ManualRecord::query()->where('status', 'pending')->whereDate('due_date', $today);
        $overdueQuery = ManualRecord::query()->where('status', 'pending')->whereDate('due_date', '<', $today);
        $totalPendingQuery = ManualRecord::query()->where('status', 'pending');
        $paidTodayQuery = ManualRecord::query()->where('status', 'paid')->whereDate('paid_date', $today);
        $paidMonthQuery = ManualRecord::query()->where('status', 'paid')->whereBetween('paid_date', [$startOfMonth, $endOfMonth]);

        return [
            'due_today_count' => (int) $dueTodayQuery->count(),
            'due_today_total' => (float) $dueTodayQuery->sum('amount'),
            'overdue_count' => (int) $overdueQuery->count(),
            'overdue_total' => (float) $overdueQuery->sum('amount'),
            'total_pending_count' => (int) $totalPendingQuery->count(),
            'total_pending_total' => (float) $totalPendingQuery->sum('amount'),
            'paid_today_total' => (float) $paidTodayQuery->sum('paid_amount'),
            'paid_month_total' => (float) $paidMonthQuery->sum('paid_amount'),
        ];
    }

    #[Computed]
    public function records(): LengthAwarePaginator
    {
        $today = Carbon::today()->toDateString();

        return ManualRecord::query()
            ->with(['supplier', 'financier', 'employee', 'bankAccount'])
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('party_name', 'like', "%{$this->search}%")
                      ->orWhere('party_phone', 'like', "%{$this->search}%")
                      ->orWhere('notes', 'like', "%{$this->search}%");
                });
            })
            ->when($this->partyType !== '', fn (Builder $q) => $q->where('party_type', $this->partyType))
            ->when($this->frequencyFilter !== '', fn (Builder $q) => $q->where('frequency', $this->frequencyFilter))
            ->when($this->tab === 'today', fn (Builder $q) => $q->where('status', 'pending')->whereDate('due_date', $today))
            ->when($this->tab === 'overdue', fn (Builder $q) => $q->where('status', 'pending')->whereDate('due_date', '<', $today))
            ->when($this->tab === 'pending', fn (Builder $q) => $q->where('status', 'pending'))
            ->when($this->tab === 'paid', fn (Builder $q) => $q->where('status', 'paid'))
            ->when($this->dateFrom !== '', fn (Builder $q) => $q->whereDate('due_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $q) => $q->whereDate('due_date', '<=', $this->dateTo))
            ->orderByRaw("
                CASE 
                    WHEN status = 'pending' AND due_date <= '{$today}' THEN 1 
                    WHEN status = 'pending' THEN 2 
                    ELSE 3 
                END ASC
            ")
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Record Modal Actions (Create / Edit)
    // ─────────────────────────────────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->authorize('create', ManualRecord::class);
        $this->resetRecordForm();
        $this->showRecordModal = true;
        Flux::modal('record-modal')->show();
    }

    public function editRecord(int $id): void
    {
        $record = ManualRecord::findOrFail($id);
        $this->authorize('update', $record);

        $this->editingRecordId = $record->id;
        $this->recordPartyType = $record->party_type;
        $this->selectedSupplierId = $record->supplier_id;
        $this->selectedFinancierId = $record->financier_id;
        $this->selectedEmployeeId = $record->employee_id;
        $this->partyName = $record->party_name;
        $this->partyPhone = (string) $record->party_phone;
        $this->amount = (float) $record->amount;
        $this->dueDate = $record->due_date ? $record->due_date->toDateString() : Carbon::today()->toDateString();
        $this->frequency = $record->frequency;
        $this->priority = $record->priority;
        $this->notes = (string) $record->notes;
        $this->isRecurring = (bool) $record->is_recurring;

        $this->showRecordModal = true;
        Flux::modal('record-modal')->show();
    }

    public function closeRecordModal(): void
    {
        $this->showRecordModal = false;
        Flux::modals()->close();
    }

    public function saveRecord(): void
    {
        $this->selectedSupplierId = ! empty($this->selectedSupplierId) ? (int) $this->selectedSupplierId : null;
        $this->selectedFinancierId = ! empty($this->selectedFinancierId) ? (int) $this->selectedFinancierId : null;
        $this->selectedEmployeeId = ! empty($this->selectedEmployeeId) ? (int) $this->selectedEmployeeId : null;
        $this->amount = ! empty($this->amount) ? (float) $this->amount : null;

        $rules = [
            'recordPartyType' => ['required', 'in:supplier,financier,employee,other'],
            'partyName' => ['required', 'string', 'max:255'],
            'partyPhone' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'dueDate' => ['required', 'date'],
            'frequency' => ['required', 'in:daily,weekly,monthly,one_time'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'isRecurring' => ['boolean'],
            'selectedSupplierId' => ['nullable', 'integer', 'exists:suppliers,id'],
            'selectedFinancierId' => ['nullable', 'integer', 'exists:financiers,id'],
            'selectedEmployeeId' => ['nullable', 'integer', 'exists:employees,id'],
        ];

        $messages = [
            'amount.required' => __('Please enter the amount to pay (₹).'),
            'amount.min' => __('Amount must be greater than ₹0.'),
            'partyName.required' => __('Please enter the party or receiver name.'),
            'dueDate.required' => __('Please enter a valid due date.'),
        ];

        $validated = $this->validate($rules, $messages);

        $data = [
            'user_id' => Auth::id(),
            'party_type' => $this->recordPartyType,
            'party_name' => trim($this->partyName),
            'party_phone' => trim($this->partyPhone) ?: null,
            'supplier_id' => $this->recordPartyType === 'supplier' ? $this->selectedSupplierId : null,
            'financier_id' => $this->recordPartyType === 'financier' ? $this->selectedFinancierId : null,
            'employee_id' => $this->recordPartyType === 'employee' ? $this->selectedEmployeeId : null,
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
            'frequency' => $this->frequency,
            'priority' => $this->priority,
            'notes' => trim($this->notes) ?: null,
            'is_recurring' => $this->frequency === 'one_time' ? false : (bool) $this->isRecurring,
        ];

        if ($this->editingRecordId) {
            $record = ManualRecord::findOrFail($this->editingRecordId);
            $this->authorize('update', $record);
            $record->update($data);

            Flux::toast(variant: 'success', text: __('Record updated successfully.'));
        } else {
            $this->authorize('create', ManualRecord::class);
            $data['status'] = 'pending';
            $data['paid_amount'] = 0;
            ManualRecord::create($data);

            Flux::toast(variant: 'success', text: __('Manual record created. You will be reminded on the due date.'));
        }

        $this->showRecordModal = false;
        Flux::modals()->close();
        $this->resetRecordForm();
        unset($this->records);
        unset($this->stats);
    }

    public function deleteRecord(int $id): void
    {
        $record = ManualRecord::findOrFail($id);
        $this->authorize('delete', $record);
        $record->delete();

        unset($this->records);
        unset($this->stats);

        Flux::toast(text: __('Manual record deleted.'), heading: __('Deleted'), variant: 'danger');
    }

    private function resetRecordForm(): void
    {
        $this->editingRecordId = null;
        $this->recordPartyType = 'supplier';
        $this->selectedSupplierId = null;
        $this->selectedFinancierId = null;
        $this->selectedEmployeeId = null;
        $this->partyName = '';
        $this->partyPhone = '';
        $this->amount = null;
        $this->dueDate = Carbon::today()->toDateString();
        $this->frequency = 'daily';
        $this->priority = 'normal';
        $this->notes = '';
        $this->isRecurring = true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Mark as Paid Modal Actions
    // ─────────────────────────────────────────────────────────────────────────

    public function openPayModal(int $id): void
    {
        $record = ManualRecord::findOrFail($id);
        $this->authorize('update', $record);

        $this->payingRecordId = $record->id;
        $this->payAmount = (float) $record->amount;
        $this->payDate = Carbon::today()->toDateString();
        $this->payMethod = 'cash';
        $this->payBankAccountId = null;
        $this->advanceNext = $record->is_recurring && $record->frequency !== 'one_time';
        $this->syncWithEntityLedger = false;

        $this->showPayModal = true;
        Flux::modal('pay-modal')->show();
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
        $this->payingRecordId = null;
        Flux::modals()->close();
    }

    public function confirmPayment(): void
    {
        if (! $this->payingRecordId) {
            return;
        }

        $record = ManualRecord::findOrFail($this->payingRecordId);
        $this->authorize('update', $record);

        $this->payBankAccountId = ! empty($this->payBankAccountId) ? (int) $this->payBankAccountId : null;
        $this->payAmount = ! empty($this->payAmount) ? (float) $this->payAmount : null;

        $messages = [
            'payAmount.required' => __('Please enter the payment amount (₹).'),
            'payAmount.min' => __('Payment amount must be greater than ₹0.'),
            'payDate.required' => __('Please select the payment date.'),
        ];

        $this->validate([
            'payAmount' => ['required', 'numeric', 'min:0.01'],
            'payDate' => ['required', 'date'],
            'payMethod' => ['required', 'string'],
            'payBankAccountId' => ['nullable', 'integer', 'exists:bank_accounts,id'],
            'advanceNext' => ['boolean'],
            'syncWithEntityLedger' => ['boolean'],
        ], $messages);

        // 1. Mark this manual record as paid and optionally advance
        $nextRecord = $record->markAsPaid(
            paidAmount: $this->payAmount,
            paymentMethod: $this->payMethod,
            bankAccountId: $this->payBankAccountId,
            paidDate: $this->payDate,
            advanceNext: $this->advanceNext
        );

        // 2. Optionally record payment in official Supplier/Financier ledger
        if ($this->syncWithEntityLedger) {
            if ($record->supplier_id) {
                SupplierPayment::create([
                    'supplier_id' => $record->supplier_id,
                    'user_id' => Auth::id(),
                    'date' => $this->payDate,
                    'type' => 'payment_made',
                    'amount' => $this->payAmount,
                    'payment_method' => $this->payMethod,
                    'notes' => __('Manual Record Payment: ').($record->notes ?: $record->party_name),
                ]);
            } elseif ($record->financier_id) {
                FinancierPayment::create([
                    'financier_id' => $record->financier_id,
                    'user_id' => Auth::id(),
                    'date' => $this->payDate,
                    'type' => 'repayment_principal',
                    'amount' => $this->payAmount,
                    'payment_method' => $this->payMethod,
                    'notes' => __('Manual Record Payment: ').($record->notes ?: $record->party_name),
                ]);
            }
        }

        // 3. If paid from a bank account, record a debit transaction
        if ($this->payBankAccountId && in_array($this->payMethod, ['bank_transfer', 'upi', 'cheque'], true)) {
            $bankAccount = BankAccount::find($this->payBankAccountId);
            if ($bankAccount) {
                BankTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'user_id' => Auth::id(),
                    'type' => 'debit',
                    'date' => $this->payDate,
                    'amount' => $this->payAmount,
                    'party_name' => $record->party_name,
                    'category' => 'Supplier / Vendor Payment',
                    'narration' => __('Manual record payment to ').$record->party_name,
                ]);
            }
        }

        $message = __('Payment of ₹:amount recorded successfully.', ['amount' => number_format($this->payAmount, 2)]);
        if ($nextRecord) {
            $message .= __(' Next reminder scheduled for :date.', ['date' => $nextRecord->due_date->format('d M Y')]);
        }

        Flux::toast(variant: 'success', text: $message);

        $this->showPayModal = false;
        Flux::modals()->close();
        $this->payingRecordId = null;
        unset($this->records);
        unset($this->stats);
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">{{ __('Manual Records & Daily Dues') }}</flux:heading>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    {{ __('Daily Payables Khata') }}
                </span>
            </div>
            <flux:text class="mt-1">
                {{ __('Track daily and scheduled payments for suppliers, financiers, staff, and manual commitments so you never forget to pay them on time.') }}
            </flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ __('Add Manual Record') }}
            </flux:button>
        </div>
    </div>

    <!-- Alert / Due Notice Banner if payments are due today or overdue -->
    @if ($this->stats['due_today_count'] > 0 || $this->stats['overdue_count'] > 0)
    <div class="rounded-xl border border-amber-300 bg-amber-50/70 p-4 dark:border-amber-900/60 dark:bg-amber-950/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
            <div class="p-2 bg-amber-500/10 rounded-lg text-amber-600 dark:text-amber-400 shrink-0">
                <flux:icon icon="bell-alert" class="size-5" />
            </div>
            <div>
                <div class="font-semibold text-amber-900 dark:text-amber-200 text-sm">
                    {{ __('Attention: Pending Payments Due Today / Overdue') }}
                </div>
                <div class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-0.5">
                    @if ($this->stats['due_today_count'] > 0)
                        <span><strong>{{ $this->stats['due_today_count'] }}</strong> {{ __('payment(s) due today totalling') }} <strong>₹{{ number_format($this->stats['due_today_total'], 2) }}</strong>.</span>
                    @endif
                    @if ($this->stats['overdue_count'] > 0)
                        <span class="text-red-700 dark:text-red-400 font-semibold ml-1"><strong>{{ $this->stats['overdue_count'] }}</strong> {{ __('overdue payment(s) totalling') }} <strong>₹{{ number_format($this->stats['overdue_total'], 2) }}</strong>.</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto">
            <button wire:click="$set('tab', 'today')" class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-amber-200/80 hover:bg-amber-300 dark:bg-amber-900/50 dark:hover:bg-amber-900/80 text-amber-900 dark:text-amber-100 transition-colors">
                {{ __('View Today’s Dues') }}
            </button>
            @if ($this->stats['overdue_count'] > 0)
            <button wire:click="$set('tab', 'overdue')" class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 dark:bg-red-950/60 dark:hover:bg-red-900/80 text-red-700 dark:text-red-300 transition-colors">
                {{ __('View Overdue') }}
            </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Top Summary Stat Cards -->
    <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        <!-- Due Today -->
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium text-amber-700 dark:text-amber-400">{{ __('Due Today') }}</flux:text>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                    {{ $this->stats['due_today_count'] }}
                </span>
            </div>
            <flux:heading size="lg" class="text-amber-600 dark:text-amber-400 font-bold">
                ₹{{ number_format($this->stats['due_today_total'], 2) }}
            </flux:heading>
        </flux:card>

        <!-- Overdue -->
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-rose-500">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium text-rose-700 dark:text-rose-400">{{ __('Overdue Missed') }}</flux:text>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                    {{ $this->stats['overdue_count'] }}
                </span>
            </div>
            <flux:heading size="lg" class="text-rose-600 dark:text-rose-400 font-bold">
                ₹{{ number_format($this->stats['overdue_total'], 2) }}
            </flux:heading>
        </flux:card>

        <!-- Total Pending -->
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-indigo-500">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium">{{ __('Total Pending Dues') }}</flux:text>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                    {{ $this->stats['total_pending_count'] }}
                </span>
            </div>
            <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400 font-bold">
                ₹{{ number_format($this->stats['total_pending_total'], 2) }}
            </flux:heading>
        </flux:card>

        <!-- Paid This Month -->
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500">
            <div class="flex items-center justify-between">
                <flux:text size="sm" class="font-medium text-emerald-700 dark:text-emerald-400">{{ __('Paid This Month') }}</flux:text>
                <span class="text-xs text-zinc-500">
                    {{ __('Today: ₹').number_format($this->stats['paid_today_total'], 0) }}
                </span>
            </div>
            <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400 font-bold">
                ₹{{ number_format($this->stats['paid_month_total'], 2) }}
            </flux:heading>
        </flux:card>
    </div>

    <!-- Filter Tabs -->
    <div class="flex border-b border-zinc-200 dark:border-zinc-700 gap-2 sm:gap-4 overflow-x-auto">
        <button wire:click="$set('tab', 'all')" class="pb-2.5 px-2 text-sm font-medium transition-colors shrink-0 {{ $tab === 'all' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent' }}">
            {{ __('All Records') }}
        </button>
        <button wire:click="$set('tab', 'today')" class="pb-2.5 px-2 text-sm font-medium transition-colors shrink-0 flex items-center gap-1.5 {{ $tab === 'today' ? 'border-b-2 border-amber-500 text-amber-600 dark:text-amber-400 font-semibold' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent' }}">
            {{ __('Due Today') }}
            @if ($this->stats['due_today_count'] > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-amber-500 text-white">
                    {{ $this->stats['due_today_count'] }}
                </span>
            @endif
        </button>
        <button wire:click="$set('tab', 'overdue')" class="pb-2.5 px-2 text-sm font-medium transition-colors shrink-0 flex items-center gap-1.5 {{ $tab === 'overdue' ? 'border-b-2 border-red-500 text-red-600 dark:text-red-400 font-semibold' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent' }}">
            {{ __('Overdue') }}
            @if ($this->stats['overdue_count'] > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-rose-500 text-white">
                    {{ $this->stats['overdue_count'] }}
                </span>
            @endif
        </button>
        <button wire:click="$set('tab', 'pending')" class="pb-2.5 px-2 text-sm font-medium transition-colors shrink-0 {{ $tab === 'pending' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent' }}">
            {{ __('Pending') }} ({{ $this->stats['total_pending_count'] }})
        </button>
        <button wire:click="$set('tab', 'paid')" class="pb-2.5 px-2 text-sm font-medium transition-colors shrink-0 {{ $tab === 'paid' ? 'border-b-2 border-emerald-500 text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent' }}">
            {{ __('Paid History') }}
        </button>
    </div>

    <!-- Filters Row -->
    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 items-end">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search party, phone, notes...')" icon="magnifying-glass" />

        <flux:select wire:model.live="partyType" :label="__('Party Type')">
            <flux:select.option value="">{{ __('All Parties') }}</flux:select.option>
            <flux:select.option value="supplier">{{ __('Suppliers') }}</flux:select.option>
            <flux:select.option value="financier">{{ __('Financiers') }}</flux:select.option>
            <flux:select.option value="employee">{{ __('Employees / Staff') }}</flux:select.option>
            <flux:select.option value="other">{{ __('Other / General') }}</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="frequencyFilter" :label="__('Frequency')">
            <flux:select.option value="">{{ __('All Frequencies') }}</flux:select.option>
            <flux:select.option value="daily">{{ __('Daily') }}</flux:select.option>
            <flux:select.option value="weekly">{{ __('Weekly') }}</flux:select.option>
            <flux:select.option value="monthly">{{ __('Monthly') }}</flux:select.option>
            <flux:select.option value="one_time">{{ __('One-Time') }}</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="datePreset" :label="__('Date Filter')">
            <flux:select.option value="">{{ __('All Dates') }}</flux:select.option>
            <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
            <flux:select.option value="this_week">{{ __('This Week') }}</flux:select.option>
            <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Custom Range') }}</flux:select.option>
        </flux:select>

        @if ($datePreset === 'custom')
        <div class="flex gap-2">
            <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
            <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
        </div>
        @endif
    </div>

    <!-- Records Table -->
    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-xs">
        <flux:table :paginate="$this->records">
            <flux:table.columns>
                <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                <flux:table.column>{{ __('Party / Contact') }}</flux:table.column>
                <flux:table.column>{{ __('Amount Owed') }}</flux:table.column>
                <flux:table.column>{{ __('Frequency') }}</flux:table.column>
                <flux:table.column>{{ __('Priority') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Notes / Purpose') }}</flux:table.column>
                <flux:table.column class="text-end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->records as $record)
                @php
                    $isOverdue = $record->isOverdue();
                    $isDueToday = $record->isDueToday();
                @endphp
                <flux:table.row :key="$record->id" class="{{ $isOverdue ? 'bg-rose-50/40 dark:bg-rose-950/20' : ($isDueToday ? 'bg-amber-50/40 dark:bg-amber-950/20' : '') }}">
                    <!-- Due Date Column -->
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100 text-xs">
                                {{ $record->due_date ? $record->due_date->format('d M Y') : '-' }}
                            </span>
                            @if ($record->isPending())
                                @if ($isOverdue)
                                    <span class="inline-flex items-center text-[10px] font-bold text-rose-600 dark:text-rose-400 mt-0.5">
                                        ⚠️ {{ __('Overdue') }} ({{ $record->due_date->diffForHumans(['parts' => 1]) }})
                                    </span>
                                @elseif ($isDueToday)
                                    <span class="inline-flex items-center text-[10px] font-bold text-amber-600 dark:text-amber-400 mt-0.5">
                                        ⚡ {{ __('Due Today!') }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-zinc-400 mt-0.5">
                                        {{ $record->due_date->diffForHumans() }}
                                    </span>
                                @endif
                            @elseif ($record->isPaid())
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-semibold">
                                    ✓ {{ __('Paid on') }} {{ $record->paid_date ? $record->paid_date->format('d M Y') : '' }}
                                </span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <!-- Party Column -->
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">
                                    {{ $record->party_name }}
                                </span>

                                <!-- Party Type Badge -->
                                @php
                                    $typeBadge = match ($record->party_type) {
                                        'supplier' => ['bg' => 'bg-indigo-100 dark:bg-indigo-950/60', 'text' => 'text-indigo-700 dark:text-indigo-300', 'label' => 'Supplier'],
                                        'financier' => ['bg' => 'bg-purple-100 dark:bg-purple-950/60', 'text' => 'text-purple-700 dark:text-purple-300', 'label' => 'Financier'],
                                        'employee' => ['bg' => 'bg-amber-100 dark:bg-amber-950/60', 'text' => 'text-amber-700 dark:text-amber-300', 'label' => 'Staff'],
                                        default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-700 dark:text-zinc-300', 'label' => 'Other'],
                                    };
                                @endphp
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase tracking-wider {{ $typeBadge['bg'] }} {{ $typeBadge['text'] }}">
                                    {{ __($typeBadge['label']) }}
                                </span>
                            </div>

                            @if ($record->party_phone)
                                <a href="tel:{{ $record->party_phone }}" class="text-xs text-zinc-500 hover:text-indigo-600 dark:hover:text-indigo-400 mt-0.5 flex items-center gap-1">
                                    <flux:icon icon="phone" class="size-3" />
                                    {{ $record->party_phone }}
                                </a>
                            @endif

                            @if ($record->supplier_id)
                                <a href="{{ route('suppliers.show', $record->supplier_id) }}" wire:navigate class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline mt-0.5">
                                    {{ __('View Supplier Ledger ↗') }}
                                </a>
                            @elseif ($record->financier_id)
                                <a href="{{ route('financiers.show', $record->financier_id) }}" wire:navigate class="text-[11px] text-purple-600 dark:text-purple-400 hover:underline mt-0.5">
                                    {{ __('View Financier Ledger ↗') }}
                                </a>
                            @elseif ($record->employee_id)
                                <a href="{{ route('employees.show', $record->employee_id) }}" wire:navigate class="text-[11px] text-amber-600 dark:text-amber-400 hover:underline mt-0.5">
                                    {{ __('View Employee Ledger ↗') }}
                                </a>
                            @endif
                        </div>
                    </flux:table.cell>

                    <!-- Amount Column -->
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-base text-zinc-900 dark:text-zinc-100">
                                ₹{{ number_format($record->amount, 2) }}
                            </span>
                            @if ($record->isPaid() && $record->paid_amount)
                                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                    {{ __('Paid: ₹') . number_format($record->paid_amount, 2) }} ({{ strtoupper($record->payment_method ?: 'cash') }})
                                </span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <!-- Frequency Column -->
                    <flux:table.cell>
                        @php
                            $freqBadge = match ($record->frequency) {
                                'daily' => ['color' => 'text-amber-700 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800', 'label' => 'Daily Repeat', 'icon' => 'arrow-path'],
                                'weekly' => ['color' => 'text-indigo-700 bg-indigo-50 dark:bg-indigo-950/40 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800', 'label' => 'Weekly', 'icon' => 'calendar'],
                                'monthly' => ['color' => 'text-purple-700 bg-purple-50 dark:bg-purple-950/40 dark:text-purple-300 border-purple-200 dark:border-purple-800', 'label' => 'Monthly', 'icon' => 'calendar-days'],
                                default => ['color' => 'text-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700', 'label' => 'One-Time', 'icon' => 'clock'],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border {{ $freqBadge['color'] }}">
                            <flux:icon :icon="$freqBadge['icon']" class="size-3" />
                            {{ __($freqBadge['label']) }}
                        </span>
                    </flux:table.cell>

                    <!-- Priority Column -->
                    <flux:table.cell>
                        @php
                            $priorityBadge = match ($record->priority) {
                                'urgent' => 'text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-950/60 border-red-300 dark:border-red-800',
                                'high' => 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-950/60 border-orange-300 dark:border-orange-800',
                                'low' => 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700',
                                default => 'text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-700',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider border {{ $priorityBadge }}">
                            {{ __($record->priority) }}
                        </span>
                    </flux:table.cell>

                    <!-- Status Column -->
                    <flux:table.cell>
                        @if ($record->isPaid())
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                {{ __('Paid') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold {{ $isOverdue ? 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300' }}">
                                <span class="size-1.5 rounded-full {{ $isOverdue ? 'bg-red-500 animate-pulse' : 'bg-amber-500' }}"></span>
                                {{ $isOverdue ? __('Overdue') : __('Pending') }}
                            </span>
                        @endif
                    </flux:table.cell>

                    <!-- Notes Column -->
                    <flux:table.cell class="max-w-xs">
                        <span class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2">
                            {{ $record->notes ?: '-' }}
                        </span>
                    </flux:table.cell>

                    <!-- Actions Column -->
                    <flux:table.cell class="text-end">
                        <div class="flex items-center justify-end gap-1.5">
                            @if ($record->isPending())
                                <flux:button size="sm" variant="primary" icon="check" wire:click="openPayModal({{ $record->id }})" title="{{ __('Mark as Paid') }}">
                                    {{ __('Pay') }}
                                </flux:button>
                            @endif

                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editRecord({{ $record->id }})" title="{{ __('Edit') }}" />

                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteRecord({{ $record->id }})" wire:confirm="{{ __('Are you sure you want to delete this manual record?') }}" class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30" title="{{ __('Delete') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-12">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <flux:icon icon="clipboard-document-check" class="size-8 text-zinc-400" />
                            <flux:heading size="md">{{ __('No manual records found') }}</flux:heading>
                            <flux:text size="sm">{{ __('Add daily or scheduled payables to keep suppliers, financiers, and staff commitments organized.') }}</flux:text>
                            <flux:button size="sm" variant="primary" class="mt-2" wire:click="openCreateModal">
                                {{ __('Add First Manual Record') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- ───────────────────────────────────────────────────────────────────────── -->
    <!-- Modal: Create / Edit Record -->
    <!-- ───────────────────────────────────────────────────────────────────────── -->
    <flux:modal name="record-modal" wire:model.self="showRecordModal" class="md:w-[560px]">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ $editingRecordId ? __('Edit Manual Record') : __('Add Manual Record / Daily Due') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Schedule a manual record of who to pay, amount, frequency, and due date.') }}</flux:text>
            </div>

            <form wire:submit.prevent="saveRecord" novalidate class="flex flex-col gap-5">
                <!-- Party Type Selector -->
                <div>
                    <flux:label>{{ __('Who do you need to pay?') }}</flux:label>
                    <div class="grid grid-cols-4 gap-2 mt-1.5">
                        <button type="button" wire:key="party-type-supplier" wire:click="setPartyType('supplier')" class="p-2.5 rounded-lg border text-xs font-semibold flex flex-col items-center gap-1 transition-colors {{ $recordPartyType === 'supplier' ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-500' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800' }}">
                            <flux:icon icon="truck" class="size-4" />
                            {{ __('Supplier') }}
                        </button>
                        <button type="button" wire:key="party-type-financier" wire:click="setPartyType('financier')" class="p-2.5 rounded-lg border text-xs font-semibold flex flex-col items-center gap-1 transition-colors {{ $recordPartyType === 'financier' ? 'border-purple-600 bg-purple-50/70 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-500' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800' }}">
                            <flux:icon icon="building-library" class="size-4" />
                            {{ __('Financier') }}
                        </button>
                        <button type="button" wire:key="party-type-employee" wire:click="setPartyType('employee')" class="p-2.5 rounded-lg border text-xs font-semibold flex flex-col items-center gap-1 transition-colors {{ $recordPartyType === 'employee' ? 'border-amber-600 bg-amber-50/70 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-500' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800' }}">
                            <flux:icon icon="user-group" class="size-4" />
                            {{ __('Staff / Labor') }}
                        </button>
                        <button type="button" wire:key="party-type-other" wire:click="setPartyType('other')" class="p-2.5 rounded-lg border text-xs font-semibold flex flex-col items-center gap-1 transition-colors {{ $recordPartyType === 'other' ? 'border-zinc-700 bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100 dark:border-zinc-500' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800' }}">
                            <flux:icon icon="user" class="size-4" />
                            {{ __('Other / Custom') }}
                        </button>
                    </div>
                </div>

                <!-- Existing Entity Dropdowns -->
                @if ($recordPartyType === 'supplier')
                    <div wire:key="container-select-supplier">
                        <flux:select wire:model.live="selectedSupplierId" :label="__('Select Existing Supplier (or type below)')">
                            <flux:select.option value="">{{ __('Select from active suppliers...') }}</flux:select.option>
                            @foreach ($this->suppliersList as $s)
                                <flux:select.option value="{{ $s->id }}">
                                    {{ $s->name }} (Balance: ₹{{ number_format($s->outstanding_balance, 0) }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @elseif ($recordPartyType === 'financier')
                    <div wire:key="container-select-financier">
                        <flux:select wire:model.live="selectedFinancierId" :label="__('Select Existing Financier (or type below)')">
                            <flux:select.option value="">{{ __('Select from active financiers...') }}</flux:select.option>
                            @foreach ($this->financiersList as $f)
                                <flux:select.option value="{{ $f->id }}">
                                    {{ $f->name }} (Payout: ₹{{ number_format($f->default_payment_amount, 0) }} / {{ $f->payout_type ?: 'payout' }}{{ $f->remaining_term ? ' • ' . $f->remaining_term : '' }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @elseif ($recordPartyType === 'employee')
                    <div wire:key="container-select-employee">
                        <flux:select wire:model.live="selectedEmployeeId" :label="__('Select Existing Staff / Employee')">
                            <flux:select.option value="">{{ __('Select staff member...') }}</flux:select.option>
                            @foreach ($this->employeesList as $e)
                                <flux:select.option value="{{ $e->id }}">
                                    {{ $e->name }} (Rate: ₹{{ number_format($e->default_daily_rate, 0) }}/day)
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                <!-- Party Name & Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <flux:input wire:model="partyName" :label="__('Party / Receiver Name')" :placeholder="__('e.g. Raja Assam Baans, Ramesh Financier, Daily Tea')" />
                    </div>
                    <div>
                        <flux:input wire:model="partyPhone" :label="__('Phone Number (Optional)')" :placeholder="__('10-digit number')" />
                    </div>
                </div>

                <!-- Amount & Due Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <flux:input type="number" step="0.01" wire:model="amount" :label="__('Amount to Pay (₹)')" placeholder="e.g. 500" icon="currency-rupee" />
                    </div>
                    <div>
                        <flux:input type="date" wire:model="dueDate" :label="__('Due Date')" />
                    </div>
                </div>

                <!-- Frequency & Priority -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:select wire:model="frequency" :label="__('Frequency / Repetition')">
                        <flux:select.option value="daily">{{ __('Daily (Every Day)') }}</flux:select.option>
                        <flux:select.option value="weekly">{{ __('Weekly (Every Week)') }}</flux:select.option>
                        <flux:select.option value="monthly">{{ __('Monthly (Every Month)') }}</flux:select.option>
                        <flux:select.option value="one_time">{{ __('One-Time (No repetition)') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="priority" :label="__('Priority')">
                        <flux:select.option value="normal">{{ __('Normal') }}</flux:select.option>
                        <flux:select.option value="high">{{ __('High Priority') }}</flux:select.option>
                        <flux:select.option value="urgent">{{ __('Urgent / Immediate') }}</flux:select.option>
                        <flux:select.option value="low">{{ __('Low Priority') }}</flux:select.option>
                    </flux:select>
                </div>

                <!-- Notes -->
                <flux:textarea wire:model="notes" :label="__('Notes / Purpose (Optional)')" :placeholder="__('e.g. Daily installment for bamboo purchase, tea and breakfast, truck transport advance...')" rows="2" />

                <!-- Recurring auto-schedule checkbox -->
                @if ($frequency !== 'one_time')
                <div class="flex items-center gap-2 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:checkbox wire:model="isRecurring" :label="__('Auto-schedule next occurrence when marked paid (e.g. tomorrow for daily dues)')" />
                </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button type="button" variant="ghost" wire:click="closeRecordModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" wire:click="saveRecord" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ $editingRecordId ? __('Update Record') : __('Save Record') }}</span>
                        <span wire:loading>{{ __('Saving...') }}</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- ───────────────────────────────────────────────────────────────────────── -->
    <!-- Modal: Mark as Paid -->
    <!-- ───────────────────────────────────────────────────────────────────────── -->
    <flux:modal name="pay-modal" wire:model.self="showPayModal" class="md:w-[500px]">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ __('Record Payment') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Mark this manual obligation as paid and optionally advance to the next due date.') }}</flux:text>
            </div>

            <form wire:submit.prevent="confirmPayment" novalidate class="flex flex-col gap-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <flux:input type="number" step="0.01" wire:model="payAmount" :label="__('Amount Paid (₹)')" placeholder="e.g. 500" icon="currency-rupee" />
                    </div>
                    <div>
                        <flux:input type="date" wire:model="payDate" :label="__('Payment Date')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <flux:select wire:model="payMethod" :label="__('Payment Method')">
                        <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                        <flux:select.option value="upi">{{ __('UPI (GPay / PhonePe / Paytm)') }}</flux:select.option>
                        <flux:select.option value="bank_transfer">{{ __('Bank Transfer (NEFT/IMPS)') }}</flux:select.option>
                        <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="payBankAccountId" :label="__('Bank Account (Optional)')">
                        <flux:select.option value="">{{ __('None / Cash Drawer') }}</flux:select.option>
                        @foreach ($this->bankAccountsList as $ba)
                            <flux:select.option value="{{ $ba->id }}">
                                {{ $ba->account_name }} (₹{{ number_format($ba->current_balance, 0) }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Auto-advance next reminder toggle -->
                <div class="flex flex-col gap-2 p-3 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-lg border border-indigo-200 dark:border-indigo-800">
                    <flux:checkbox wire:model="advanceNext" :label="__('Schedule next occurrence for tomorrow / next cycle')" />
                    <span class="text-xs text-indigo-700 dark:text-indigo-300 ml-6">
                        {{ __('Keeps your daily habit active by creating tomorrow’s pending reminder automatically.') }}
                    </span>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button type="button" variant="ghost" wire:click="closePayModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check" wire:click="confirmPayment" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('Confirm Paid') }}</span>
                        <span wire:loading>{{ __('Saving...') }}</span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

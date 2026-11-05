<?php

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Title('Bank Passbook & Accounts')] class extends Component {
    use WithPagination, WithFileUploads;

    #[Url]
    public string $search = '';

    #[Url]
    public string $accountId = '';

    #[Url]
    public string $typeFilter = ''; // '', 'credit', 'debit'

    #[Url]
    public string $categoryFilter = '';

    #[Url]
    public string $datePreset = 'month';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    // Transaction Modal State
    public bool $showTransactionModal = false;
    public ?int $editingTransactionId = null;
    public ?int $txAccountId = null;
    public string $txType = 'credit'; // 'credit' or 'debit'
    public string $txDate = '';
    public ?float $txAmount = null;
    public string $txCategory = 'Customer Payment';
    public string $txPartyName = '';
    public string $txReferenceNumber = '';
    public string $txDescription = '';
    public $txReceipt = null;
    public ?string $existingReceiptPath = null;

    // Bank Account Modal State
    public bool $showAccountModal = false;
    public ?int $editingAccountId = null;
    public string $accBankName = '';
    public string $accAccountName = '';
    public string $accAccountNumber = '';
    public string $accIfscCode = '';
    public string $accBranch = '';
    public ?float $accOpeningBalance = 0.00;
    public string $accOpeningDate = '';
    public string $accColor = 'emerald';
    public bool $accIsActive = true;
    public string $accNotes = '';

    // Transfer Modal State
    public bool $showTransferModal = false;
    public ?int $transferFromAccountId = null;
    public ?int $transferToAccountId = null;
    public string $transferDate = '';
    public ?float $transferAmount = null;
    public string $transferReference = '';
    public string $transferNotes = '';

    // Receipt Preview Modal
    public bool $showReceiptModal = false;
    public ?string $previewReceiptUrl = null;
    public ?string $previewReceiptName = null;

    public function mount(): void
    {
        $this->authorize('viewAny', BankAccount::class);

        if (! $this->dateFrom || ! $this->dateTo) {
            $this->applyPreset($this->datePreset);
        }

        // Auto-seed a default bank account if none exists yet so user has immediate out-of-the-box experience
        if (BankAccount::count() === 0 && (Auth::user()?->isAdmin() || Auth::user()?->isManager())) {
            BankAccount::create([
                'user_id' => Auth::id(),
                'bank_name' => 'Main Current Account',
                'account_name' => 'Primary Business A/C',
                'account_number' => '1234567890',
                'ifsc_code' => 'HDFC0001234',
                'branch' => 'Main Branch',
                'opening_balance' => 0.00,
                'opening_date' => now()->toDateString(),
                'current_balance' => 0.00,
                'color' => 'emerald',
                'is_active' => true,
            ]);
        }
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'accountId', 'typeFilter', 'categoryFilter', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->accountId = '';
        $this->categoryFilter = '';
        $this->typeFilter = '';
        $this->datePreset = 'month';
        $this->applyPreset('month');
        $this->resetPage();
    }

    public function setPreset(string $preset): void
    {
        $this->datePreset = $preset;
        $this->applyPreset($preset);
        $this->resetPage();
    }

    protected function applyPreset(string $preset): void
    {
        switch ($preset) {
            case 'today':
                $this->dateFrom = now()->toDateString();
                $this->dateTo = now()->toDateString();
                break;
            case 'yesterday':
                $this->dateFrom = now()->subDay()->toDateString();
                $this->dateTo = now()->subDay()->toDateString();
                break;
            case 'week':
                $this->dateFrom = now()->startOfWeek()->toDateString();
                $this->dateTo = now()->endOfWeek()->toDateString();
                break;
            case 'month':
                $this->dateFrom = now()->startOfMonth()->toDateString();
                $this->dateTo = now()->endOfMonth()->toDateString();
                break;
            case 'custom':
                if (! $this->dateFrom) {
                    $this->dateFrom = now()->startOfMonth()->toDateString();
                }
                if (! $this->dateTo) {
                    $this->dateTo = now()->toDateString();
                }
                break;
            case 'all':
            default:
                $this->dateFrom = '';
                $this->dateTo = '';
                break;
        }
    }

    public function filterByAccount(string $id = ''): void
    {
        $this->accountId = $id;
        $this->resetPage();
    }

    #[Computed]
    public function accounts()
    {
        return BankAccount::query()
            ->withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('bank_name')
            ->get();
    }

    #[Computed]
    public function totalLiquidity(): float
    {
        return (float) BankAccount::where('is_active', true)->sum('current_balance');
    }

    #[Computed]
    public function todayCredits(): float
    {
        $query = BankTransaction::query()
            ->where('type', 'credit')
            ->whereDate('date', now()->toDateString());

        if ($this->accountId) {
            $query->where('bank_account_id', $this->accountId);
        }

        return (float) $query->sum('amount');
    }

    #[Computed]
    public function todayDebits(): float
    {
        $query = BankTransaction::query()
            ->where('type', 'debit')
            ->whereDate('date', now()->toDateString());

        if ($this->accountId) {
            $query->where('bank_account_id', $this->accountId);
        }

        return (float) $query->sum('amount');
    }

    #[Computed]
    public function todayNet(): float
    {
        return $this->todayCredits - $this->todayDebits;
    }

    #[Computed]
    public function availableCategories(): array
    {
        $standardCredit = [
            'Customer Payment' => '📥 Customer Payment',
            'Cash Deposit' => '💵 Cash Deposit',
            'Owner Capital' => '💼 Owner Investment / Capital',
            'Loan Received' => '🏦 Loan / Financier Receipt',
            'Interest / Dividend' => '📈 Bank Interest / Returns',
            'Tax Refund' => '📑 Tax / GST Refund',
            'Rental Income' => '🏠 Rental Income',
            'Internal Transfer' => '⇄ Internal Transfer',
            'General Credit' => '🟢 General Credit',
        ];

        $standardDebit = [
            'Vendor Payment' => '📤 Vendor / Supplier Payment',
            'Salary / Wages' => '👷 Salary / Labour Wages',
            'Cash Withdrawal' => '🏧 Cash / ATM Withdrawal',
            'Rent / Lease' => '🏢 Rent / Property Lease',
            'Utility / Electricity' => '⚡ Utility / Electricity Bill',
            'Owner Drawing' => '👤 Owner Drawing / Personal',
            'Loan EMI' => '🏦 Loan EMI / Interest Paid',
            'Taxes / GST' => '📑 Tax / GST Payment',
            'Bank Charges' => '💳 Bank Fees / Service Charges',
            'Internal Transfer' => '⇄ Internal Transfer',
            'General Debit' => '🔴 General Debit',
        ];

        if ($this->typeFilter === 'credit') {
            $categories = $standardCredit;
        } elseif ($this->typeFilter === 'debit') {
            $categories = $standardDebit;
        } else {
            $categories = array_merge($standardCredit, $standardDebit);
        }

        // Fetch any additional distinct category recorded in the database
        $dbCategories = BankTransaction::query()
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->all();

        foreach ($dbCategories as $cat) {
            if (! isset($categories[$cat])) {
                $categories[$cat] = '📁 ' . $cat;
            }
        }

        return $categories;
    }

    #[Computed]
    public function filteredStats(): array
    {
        $query = BankTransaction::query();

        if ($this->accountId) {
            $query->where('bank_account_id', $this->accountId);
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('party_name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('reference_number', 'like', $term)
                    ->orWhere('category', 'like', $term)
                    ->orWhereHas('account', fn($aq) => $aq->where('bank_name', 'like', $term)->orWhere('account_name', 'like', $term));
            });
        }

        $countQuery = clone $query;
        if ($this->typeFilter) {
            $countQuery->where('type', $this->typeFilter);
        }
        $count = (int) $countQuery->count();

        $credits = (float) (clone $query)->where('type', 'credit')->sum('amount');
        $debits = (float) (clone $query)->where('type', 'debit')->sum('amount');

        return [
            'credits' => $credits,
            'debits' => $debits,
            'net' => $credits - $debits,
            'count' => $count,
        ];
    }

    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        $query = BankTransaction::query()
            ->with(['account', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($this->accountId) {
            $query->where('bank_account_id', $this->accountId);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('party_name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('reference_number', 'like', $term)
                    ->orWhere('category', 'like', $term)
                    ->orWhereHas('account', fn($aq) => $aq->where('bank_name', 'like', $term)->orWhere('account_name', 'like', $term));
            });
        }

        return $query->paginate(20);
    }

    // ================= TRANSACTION MODAL ACTIONS =================

    public function openTransactionModal(string $type = 'credit', ?int $id = null): void
    {
        $this->authorize('create', BankTransaction::class);
        $this->resetValidation();

        if ($id) {
            $tx = BankTransaction::findOrFail($id);
            $this->authorize('update', $tx);

            $this->editingTransactionId = $tx->id;
            $this->txAccountId = $tx->bank_account_id;
            $this->txType = $tx->type;
            $this->txDate = $tx->date->toDateString();
            $this->txAmount = (float) $tx->amount;
            $this->txCategory = $tx->category;
            $this->txPartyName = (string) $tx->party_name;
            $this->txReferenceNumber = (string) $tx->reference_number;
            $this->txDescription = (string) $tx->description;
            $this->existingReceiptPath = $tx->receipt_path;
            $this->txReceipt = null;
        } else {
            $this->editingTransactionId = null;
            $this->txType = $type;
            $this->txAccountId = $this->accountId ? (int) $this->accountId : (BankAccount::where('is_active', true)->value('id') ?? BankAccount::value('id'));
            $this->txDate = now()->toDateString();
            $this->txAmount = null;
            $this->txCategory = $type === 'credit' ? 'Customer Payment' : 'Vendor Payment';
            $this->txPartyName = '';
            $this->txReferenceNumber = '';
            $this->txDescription = '';
            $this->existingReceiptPath = null;
            $this->txReceipt = null;
        }

        $this->showTransactionModal = true;
    }

    public function saveTransaction(): void
    {
        $this->validate([
            'txAccountId' => ['required', 'exists:bank_accounts,id'],
            'txType' => ['required', 'in:credit,debit'],
            'txDate' => ['required', 'date'],
            'txAmount' => ['required', 'numeric', 'gt:0'],
            'txCategory' => ['required', 'string', 'max:100'],
            'txPartyName' => ['nullable', 'string', 'max:255'],
            'txReferenceNumber' => ['nullable', 'string', 'max:100'],
            'txDescription' => ['nullable', 'string', 'max:1000'],
            'txReceipt' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:10240'],
        ]);

        $receiptPath = $this->existingReceiptPath;
        if ($this->txReceipt) {
            if ($receiptPath && Storage::disk('public')->exists($receiptPath)) {
                Storage::disk('public')->delete($receiptPath);
            }
            $receiptPath = $this->txReceipt->store('bank-receipts', 'public');
        }

        if ($this->editingTransactionId) {
            $tx = BankTransaction::findOrFail($this->editingTransactionId);
            $this->authorize('update', $tx);

            $oldAccountId = $tx->bank_account_id;

            $tx->update([
                'bank_account_id' => $this->txAccountId,
                'date' => $this->txDate,
                'type' => $this->txType,
                'amount' => $this->txAmount,
                'category' => $this->txCategory,
                'party_name' => $this->txPartyName ?: null,
                'reference_number' => $this->txReferenceNumber ?: null,
                'description' => $this->txDescription ?: null,
                'receipt_path' => $receiptPath,
            ]);

            if ($oldAccountId !== $this->txAccountId) {
                BankAccount::find($oldAccountId)?->recalculateBalance();
            }
            $tx->account?->recalculateBalance();

            Flux::toast(variant: 'success', text: __('Bank transaction updated successfully.'));
        } else {
            $this->authorize('create', BankTransaction::class);

            $tx = BankTransaction::create([
                'bank_account_id' => $this->txAccountId,
                'user_id' => Auth::id(),
                'date' => $this->txDate,
                'type' => $this->txType,
                'amount' => $this->txAmount,
                'category' => $this->txCategory,
                'party_name' => $this->txPartyName ?: null,
                'reference_number' => $this->txReferenceNumber ?: null,
                'description' => $this->txDescription ?: null,
                'receipt_path' => $receiptPath,
            ]);

            $tx->account?->recalculateBalance();

            Flux::toast(variant: 'success', text: $this->txType === 'credit' ? __('Credit recorded successfully.') : __('Debit recorded successfully.'));
        }

        $this->showTransactionModal = false;
        $this->reset(['txReceipt', 'editingTransactionId']);
    }

    public function deleteTransaction(int $id): void
    {
        $tx = BankTransaction::findOrFail($id);
        $this->authorize('delete', $tx);

        $account = $tx->account;
        if ($tx->receipt_path && Storage::disk('public')->exists($tx->receipt_path)) {
            Storage::disk('public')->delete($tx->receipt_path);
        }

        $tx->delete();
        $account?->recalculateBalance();

        Flux::toast(variant: 'success', text: __('Transaction deleted.'));
    }

    public function removeExistingReceipt(): void
    {
        if ($this->existingReceiptPath && Storage::disk('public')->exists($this->existingReceiptPath)) {
            Storage::disk('public')->delete($this->existingReceiptPath);
        }
        $this->existingReceiptPath = null;

        if ($this->editingTransactionId) {
            BankTransaction::where('id', $this->editingTransactionId)->update(['receipt_path' => null]);
        }
    }

    // ================= BANK ACCOUNT MODAL ACTIONS =================

    public function openAccountModal(?int $id = null): void
    {
        $this->resetValidation();

        if ($id) {
            $account = BankAccount::findOrFail($id);
            $this->authorize('update', $account);

            $this->editingAccountId = $account->id;
            $this->accBankName = $account->bank_name;
            $this->accAccountName = $account->account_name;
            $this->accAccountNumber = (string) $account->account_number;
            $this->accIfscCode = (string) $account->ifsc_code;
            $this->accBranch = (string) $account->branch;
            $this->accOpeningBalance = (float) $account->opening_balance;
            $this->accOpeningDate = $account->opening_date ? $account->opening_date->toDateString() : now()->toDateString();
            $this->accColor = $account->color ?? 'emerald';
            $this->accIsActive = (bool) $account->is_active;
            $this->accNotes = (string) $account->notes;
        } else {
            $this->authorize('create', BankAccount::class);

            $this->editingAccountId = null;
            $this->accBankName = '';
            $this->accAccountName = '';
            $this->accAccountNumber = '';
            $this->accIfscCode = '';
            $this->accBranch = '';
            $this->accOpeningBalance = 0.00;
            $this->accOpeningDate = now()->toDateString();
            $this->accColor = 'emerald';
            $this->accIsActive = true;
            $this->accNotes = '';
        }

        $this->showAccountModal = true;
    }

    public function saveAccount(): void
    {
        $this->validate([
            'accBankName' => ['required', 'string', 'max:150'],
            'accAccountName' => ['required', 'string', 'max:150'],
            'accAccountNumber' => ['nullable', 'string', 'max:50'],
            'accIfscCode' => ['nullable', 'string', 'max:30'],
            'accBranch' => ['nullable', 'string', 'max:100'],
            'accOpeningBalance' => ['required', 'numeric'],
            'accOpeningDate' => ['nullable', 'date'],
            'accColor' => ['required', 'string', 'in:emerald,sky,indigo,amber,rose,purple,teal'],
            'accIsActive' => ['boolean'],
            'accNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->editingAccountId) {
            $account = BankAccount::findOrFail($this->editingAccountId);
            $this->authorize('update', $account);

            $account->update([
                'bank_name' => $this->accBankName,
                'account_name' => $this->accAccountName,
                'account_number' => $this->accAccountNumber ?: null,
                'ifsc_code' => $this->accIfscCode ?: null,
                'branch' => $this->accBranch ?: null,
                'opening_balance' => $this->accOpeningBalance,
                'opening_date' => $this->accOpeningDate ?: null,
                'color' => $this->accColor,
                'is_active' => $this->accIsActive,
                'notes' => $this->accNotes ?: null,
            ]);

            $account->recalculateBalance();
            Flux::toast(variant: 'success', text: __('Bank account updated.'));
        } else {
            $this->authorize('create', BankAccount::class);

            $account = BankAccount::create([
                'user_id' => Auth::id(),
                'bank_name' => $this->accBankName,
                'account_name' => $this->accAccountName,
                'account_number' => $this->accAccountNumber ?: null,
                'ifsc_code' => $this->accIfscCode ?: null,
                'branch' => $this->accBranch ?: null,
                'opening_balance' => $this->accOpeningBalance,
                'opening_date' => $this->accOpeningDate ?: null,
                'current_balance' => $this->accOpeningBalance,
                'color' => $this->accColor,
                'is_active' => $this->accIsActive,
                'notes' => $this->accNotes ?: null,
            ]);

            Flux::toast(variant: 'success', text: __('New bank account added.'));
        }

        $this->showAccountModal = false;
        $this->reset(['editingAccountId']);
    }

    public function deleteAccount(int $id): void
    {
        $account = BankAccount::findOrFail($id);
        $this->authorize('delete', $account);

        $txCount = $account->transactions()->count();
        if ($txCount > 0) {
            Flux::toast(variant: 'danger', text: __('Cannot delete account with existing transactions. Deactivate it instead.'));
            return;
        }

        $account->delete();
        if ($this->accountId == (string) $id) {
            $this->accountId = '';
        }

        Flux::toast(variant: 'success', text: __('Bank account deleted.'));
    }

    // ================= INTERNAL TRANSFER ACTIONS =================

    public function openTransferModal(): void
    {
        $this->authorize('create', BankTransaction::class);
        $this->resetValidation();

        $accounts = $this->accounts;
        $this->transferFromAccountId = $accounts->first()?->id;
        $this->transferToAccountId = $accounts->count() > 1 ? $accounts->skip(1)->first()?->id : null;
        $this->transferDate = now()->toDateString();
        $this->transferAmount = null;
        $this->transferReference = '';
        $this->transferNotes = '';

        $this->showTransferModal = true;
    }

    public function executeTransfer(): void
    {
        $this->authorize('create', BankTransaction::class);

        $this->validate([
            'transferFromAccountId' => ['required', 'exists:bank_accounts,id'],
            'transferToAccountId' => ['required', 'exists:bank_accounts,id', 'different:transferFromAccountId'],
            'transferDate' => ['required', 'date'],
            'transferAmount' => ['required', 'numeric', 'gt:0'],
            'transferReference' => ['nullable', 'string', 'max:100'],
            'transferNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $fromAcc = BankAccount::findOrFail($this->transferFromAccountId);
        $toAcc = BankAccount::findOrFail($this->transferToAccountId);

        // 1. Debit from source account
        $debitTx = BankTransaction::create([
            'bank_account_id' => $fromAcc->id,
            'user_id' => Auth::id(),
            'date' => $this->transferDate,
            'type' => 'debit',
            'amount' => $this->transferAmount,
            'category' => 'Internal Transfer',
            'party_name' => 'To: ' . $toAcc->bank_name . ' (' . $toAcc->account_name . ')',
            'reference_number' => $this->transferReference ?: 'TXFR-' . strtoupper(bin2hex(random_bytes(3))),
            'description' => 'Transfer to ' . $toAcc->bank_name . ($this->transferNotes ? ' | ' . $this->transferNotes : ''),
        ]);

        // 2. Credit to destination account
        $creditTx = BankTransaction::create([
            'bank_account_id' => $toAcc->id,
            'user_id' => Auth::id(),
            'date' => $this->transferDate,
            'type' => 'credit',
            'amount' => $this->transferAmount,
            'category' => 'Internal Transfer',
            'party_name' => 'From: ' . $fromAcc->bank_name . ' (' . $fromAcc->account_name . ')',
            'reference_number' => $debitTx->reference_number,
            'description' => 'Transfer from ' . $fromAcc->bank_name . ($this->transferNotes ? ' | ' . $this->transferNotes : ''),
        ]);

        $fromAcc->recalculateBalance();
        $toAcc->recalculateBalance();

        $this->showTransferModal = false;
        Flux::toast(variant: 'success', text: __('Transferred ₹:amount between accounts successfully.', ['amount' => number_format((float) $this->transferAmount, 2)]));
    }

    // ================= RECEIPT PREVIEW =================

    public function previewReceipt(int $id): void
    {
        $tx = BankTransaction::findOrFail($id);
        $this->authorize('view', $tx);

        if ($tx->receipt_path) {
            $this->previewReceiptUrl = Storage::disk('public')->url($tx->receipt_path);
            $this->previewReceiptName = ($tx->reference_number ?: 'Tx #' . $tx->id) . ' - ' . $tx->category;
            $this->showReceiptModal = true;
        }
    }

    // ================= CSV EXPORT =================

    public function exportCsv(): StreamedResponse
    {
        $this->authorize('viewAny', BankAccount::class);

        $query = BankTransaction::query()
            ->with(['account', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($this->accountId) {
            $query->where('bank_account_id', $this->accountId);
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }
        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }
        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('party_name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('reference_number', 'like', $term)
                    ->orWhere('category', 'like', $term);
            });
        }

        $records = $query->get();
        $filename = 'Bank_Passbook_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Date', 'Bank Account', 'Account Number', 'Type', 'Amount (INR)', 'Category', 'Party / Payee', 'Reference / UTR', 'Description', 'Recorded By']);

            foreach ($records as $tx) {
                fputcsv($handle, [
                    $tx->id,
                    $tx->date->format('Y-m-d'),
                    $tx->account?->bank_name . ' (' . $tx->account?->account_name . ')',
                    $tx->account?->account_number ?? '',
                    strtoupper($tx->type),
                    $tx->type === 'credit' ? '+' . $tx->amount : '-' . $tx->amount,
                    $tx->category,
                    $tx->party_name ?? '',
                    $tx->reference_number ?? '',
                    $tx->description ?? '',
                    $tx->user?->name ?? 'System',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Header with Quick Action Buttons -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/20">
                    <flux:icon name="building-library" class="size-6" />
                </div>
                <div>
                    <flux:heading size="xl" class="font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        {{ __('Bank Account Passbook') }}
                    </flux:heading>
                    <flux:subheading class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Track daily credits, debits, running balances, and multi-bank liquidity') }}
                    </flux:subheading>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Record Credit (+) Button -->
            <flux:button
                variant="primary"
                icon="arrow-down-left"
                class="bg-emerald-600 hover:bg-emerald-700 text-white border-0 shadow-sm shadow-emerald-600/30"
                wire:click="openTransactionModal('credit')"
            >
                {{ __('+ Credit (Money In)') }}
            </flux:button>

            <!-- Record Debit (-) Button -->
            <flux:button
                variant="filled"
                icon="arrow-up-right"
                class="bg-rose-600 hover:bg-rose-700 text-white border-0 shadow-sm shadow-rose-600/30"
                wire:click="openTransactionModal('debit')"
            >
                {{ __('— Debit (Money Out)') }}
            </flux:button>

            <!-- Internal Transfer Button -->
            @if ($this->accounts->count() > 1)
            <flux:button
                variant="subtle"
                icon="arrows-right-left"
                class="text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-50 dark:hover:bg-indigo-950/30"
                wire:click="openTransferModal"
                title="{{ __('Transfer money between your bank accounts') }}"
            >
                <span class="hidden sm:inline">{{ __('Transfer') }}</span>
            </flux:button>
            @endif

            <!-- Manage Bank Accounts Modal Button -->
            @can('create', App\Models\BankAccount::class)
            <flux:button
                variant="subtle"
                icon="plus-circle"
                wire:click="openAccountModal(null)"
                title="{{ __('Add new bank account') }}"
            >
                <span class="hidden md:inline">{{ __('Add Account') }}</span>
            </flux:button>
            @endcan

            <!-- CSV Statement Export -->
            <flux:button
                variant="ghost"
                icon="arrow-down-tray"
                wire:click="exportCsv"
                title="{{ __('Export filtered transactions to CSV') }}"
            />
        </div>
    </div>

    <!-- Top Summary Stat Cards -->
    <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        <!-- Total Bank Liquidity -->
        <div class="relative overflow-hidden rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent p-5 dark:border-emerald-800/40 dark:from-emerald-950/40 dark:via-zinc-900/60 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">{{ __('Total Bank Liquidity') }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                    <flux:icon name="banknotes" class="size-4" />
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <h3 class="text-2xl font-black tracking-tight text-emerald-700 dark:text-emerald-400">
                    ₹{{ number_format($this->totalLiquidity, 2) }}
                </h3>
            </div>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Across :count active bank accounts', ['count' => $this->accounts->where('is_active', true)->count()]) }}
            </p>
        </div>

        <!-- Today Credits -->
        <div class="relative overflow-hidden rounded-2xl border border-teal-200/70 bg-gradient-to-br from-teal-500/10 via-teal-500/5 to-transparent p-5 dark:border-teal-800/40 dark:from-teal-950/40 dark:via-zinc-900/60 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-teal-800 dark:text-teal-300">{{ __('Today Credits (In)') }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-500/15 text-teal-600 dark:text-teal-400">
                    <flux:icon name="arrow-down-left" class="size-4" />
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black tracking-tight text-teal-700 dark:text-teal-400">
                    +₹{{ number_format($this->todayCredits, 2) }}
                </h3>
            </div>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Received today across bank ledgers') }}
            </p>
        </div>

        <!-- Today Debits -->
        <div class="relative overflow-hidden rounded-2xl border border-rose-200/70 bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-transparent p-5 dark:border-rose-800/40 dark:from-rose-950/40 dark:via-zinc-900/60 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-800 dark:text-rose-300">{{ __('Today Debits (Out)') }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500/15 text-rose-600 dark:text-rose-400">
                    <flux:icon name="arrow-up-right" class="size-4" />
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black tracking-tight text-rose-700 dark:text-rose-400">
                    -₹{{ number_format($this->todayDebits, 2) }}
                </h3>
            </div>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Paid out today from accounts') }}
            </p>
        </div>

        <!-- Today Net Cash Flow -->
        <div class="relative overflow-hidden rounded-2xl border {{ $this->todayNet >= 0 ? 'border-emerald-200/70 bg-emerald-50/30 dark:border-emerald-800/40 dark:bg-emerald-950/20' : 'border-rose-200/70 bg-rose-50/30 dark:border-rose-800/40 dark:bg-rose-950/20' }} p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider {{ $this->todayNet >= 0 ? 'text-emerald-800 dark:text-emerald-300' : 'text-rose-800 dark:text-rose-300' }}">{{ __('Today Net Cashflow') }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $this->todayNet >= 0 ? 'bg-emerald-500/15 text-emerald-600' : 'bg-rose-500/15 text-rose-600' }}">
                    <flux:icon name="scale" class="size-4" />
                </span>
            </div>
            <div class="mt-2">
                <h3 class="text-2xl font-black tracking-tight {{ $this->todayNet >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                    {{ $this->todayNet >= 0 ? '+' : '' }}₹{{ number_format($this->todayNet, 2) }}
                </h3>
            </div>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Net movement for today') }}
            </p>
        </div>
    </div>

    <!-- Multi-Bank Account Selector Carousel Cards -->
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <flux:heading size="sm" class="font-semibold text-zinc-700 dark:text-zinc-300">
                {{ __('Select Bank Account') }}
            </flux:heading>
            <span class="text-xs text-zinc-500">
                {{ __('Click any account card to filter passbook') }}
            </span>
        </div>

        <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <!-- All Accounts Card -->
            <button
                type="button"
                wire:click="filterByAccount('')"
                class="group text-start transition-all relative flex flex-col justify-between p-4 rounded-xl border {{ $accountId === '' ? 'ring-2 ring-emerald-500 border-emerald-500 bg-white dark:bg-zinc-800 shadow-md' : 'border-zinc-200 dark:border-zinc-700/80 bg-zinc-50/50 hover:bg-white dark:bg-zinc-900/40 dark:hover:bg-zinc-800/80' }}"
            >
                <div class="flex items-start justify-between w-full">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                            <flux:icon name="squares-2x2" class="size-4" />
                        </span>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ __('Consolidated') }}</span>
                    </div>
                    @if ($accountId === '')
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-500/10 px-1.5 py-0.5 rounded-full border border-emerald-500/20">Active</span>
                    @endif
                </div>

                <div class="mt-3">
                    <span class="text-[11px] uppercase tracking-wide text-zinc-500 block">{{ __('All Bank Accounts') }}</span>
                    <span class="text-lg font-black text-zinc-900 dark:text-zinc-100">
                        ₹{{ number_format($this->totalLiquidity, 2) }}
                    </span>
                </div>
            </button>

            <!-- Individual Bank Accounts -->
            @foreach ($this->accounts as $acc)
                @php
                    $isSelected = ($accountId === (string) $acc->id);
                    $colorMap = [
                        'emerald' => 'border-emerald-500 bg-emerald-500/10 text-emerald-600',
                        'sky' => 'border-sky-500 bg-sky-500/10 text-sky-600',
                        'indigo' => 'border-indigo-500 bg-indigo-500/10 text-indigo-600',
                        'amber' => 'border-amber-500 bg-amber-500/10 text-amber-600',
                        'rose' => 'border-rose-500 bg-rose-500/10 text-rose-600',
                        'purple' => 'border-purple-500 bg-purple-500/10 text-purple-600',
                        'teal' => 'border-teal-500 bg-teal-500/10 text-teal-600',
                    ];
                    $accent = $colorMap[$acc->color ?? 'emerald'] ?? $colorMap['emerald'];
                @endphp
                <div
                    class="group relative flex flex-col justify-between p-4 rounded-xl border transition-all text-start {{ $isSelected ? 'ring-2 ring-emerald-500 border-emerald-500 bg-white dark:bg-zinc-800 shadow-md' : 'border-zinc-200 dark:border-zinc-700/80 bg-zinc-50/50 hover:bg-white dark:bg-zinc-900/40 dark:hover:bg-zinc-800/80' }}"
                >
                    <div class="flex items-start justify-between w-full">
                        <button type="button" wire:click="filterByAccount('{{ $acc->id }}')" class="flex items-center gap-2 min-w-0 flex-1 text-start">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $accent }}">
                                <flux:icon name="building-library" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 block truncate">{{ $acc->bank_name }}</span>
                                <span class="text-[10px] text-zinc-500 block truncate">{{ $acc->account_name }}</span>
                            </div>
                        </button>

                        <div class="flex items-center gap-1 shrink-0">
                            @can('update', $acc)
                            <button
                                type="button"
                                wire:click.stop="openAccountModal({{ $acc->id }})"
                                class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                                title="{{ __('Edit Account Details') }}"
                            >
                                <flux:icon name="pencil-square" class="size-3.5" />
                            </button>
                            @endcan
                        </div>
                    </div>

                    <button type="button" wire:click="filterByAccount('{{ $acc->id }}')" class="mt-3 w-full text-start">
                        <div class="flex items-center justify-between text-[11px] text-zinc-500">
                            <span>{{ $acc->maskedAccountNumber() }}</span>
                            <span>{{ $acc->transactions_count }} txns</span>
                        </div>
                        <span class="text-lg font-black text-zinc-900 dark:text-zinc-100 block mt-0.5">
                            ₹{{ number_format((float) $acc->current_balance, 2) }}
                        </span>
                    </button>
                </div>
            @endforeach

            <!-- Add New Account Quick Action Card -->
            @can('create', App\Models\BankAccount::class)
            <button
                type="button"
                wire:click="openAccountModal(null)"
                class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-emerald-50/20 dark:hover:bg-emerald-950/10 text-zinc-500 hover:text-emerald-600 transition-all text-center"
            >
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 group-hover:bg-emerald-100">
                    <flux:icon name="plus" class="size-4" />
                </div>
                <span class="text-xs font-medium">{{ __('+ Add Bank Account') }}</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Filter Toolbar -->
    <flux:card class="flex flex-col gap-4 p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between flex-wrap">
            <!-- Left: Search, Bank Account & Category Filter Dropdown -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 min-w-[280px]">
                <!-- Search Input -->
                <div class="w-full sm:w-60">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search party, narration, UTR #...') }}"
                        icon="magnifying-glass"
                        clearable
                    />
                </div>

                <!-- Bank Account Dropdown Filter -->
                <div class="w-full sm:w-52">
                    <flux:select wire:model.live="accountId" :placeholder="__('All Bank Accounts')">
                        <flux:select.option value="">{{ __('All Bank Accounts') }}</flux:select.option>
                        @foreach ($this->accounts as $acc)
                            <flux:select.option value="{{ $acc->id }}">
                                {{ $acc->bank_name }} ({{ $acc->maskedAccountNumber() ?: $acc->account_name }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Category Dropdown Filter -->
                <div class="w-full sm:w-52">
                    <flux:select wire:model.live="categoryFilter" :placeholder="__('All Categories')">
                        <flux:select.option value="">{{ __('All Categories') }}</flux:select.option>
                        @foreach ($this->availableCategories as $val => $label)
                            <flux:select.option value="{{ $val }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                @if ($search || $accountId || $categoryFilter || $typeFilter || $datePreset !== 'month')
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors whitespace-nowrap self-start sm:self-center rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800"
                        title="{{ __('Reset all filters') }}"
                    >
                        <flux:icon name="x-mark" class="size-3.5" />
                        <span>{{ __('Reset') }}</span>
                    </button>
                @endif
            </div>

            <!-- Right: Date Preset Pills & Transaction Type Segmented Toggle -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Date Preset Pills -->
                <div class="flex flex-wrap items-center gap-1">
                    @foreach (['today' => __('Today'), 'yesterday' => __('Yesterday'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time'), 'custom' => __('Custom')] as $key => $label)
                        <button
                            type="button"
                            wire:click="setPreset('{{ $key }}')"
                            class="px-2.5 py-1 text-xs font-medium rounded-lg transition-all {{ $datePreset === $key ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-sm' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <!-- Transaction Type Segmented Toggle -->
                <div class="inline-flex rounded-lg bg-zinc-100 dark:bg-zinc-800 p-0.5">
                    <button
                        type="button"
                        wire:click="$set('typeFilter', '')"
                        class="px-3 py-1 text-xs font-medium rounded-md transition-colors {{ $typeFilter === '' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm font-semibold' : 'text-zinc-600 dark:text-zinc-400' }}"
                    >
                        {{ __('All') }}
                    </button>
                    <button
                        type="button"
                        wire:click="$set('typeFilter', 'credit')"
                        class="px-3 py-1 text-xs font-medium rounded-md transition-colors {{ $typeFilter === 'credit' ? 'bg-emerald-600 text-white shadow-sm font-semibold' : 'text-emerald-700 dark:text-emerald-400 hover:text-emerald-600' }}"
                    >
                        {{ __('Credits (+)') }}
                    </button>
                    <button
                        type="button"
                        wire:click="$set('typeFilter', 'debit')"
                        class="px-3 py-1 text-xs font-medium rounded-md transition-colors {{ $typeFilter === 'debit' ? 'bg-rose-600 text-white shadow-sm font-semibold' : 'text-rose-700 dark:text-rose-400 hover:text-rose-600' }}"
                    >
                        {{ __('Debits (—)') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Custom Date Range Inputs (if custom preset) -->
        @if ($datePreset === 'custom')
        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Date Range:') }}</span>
            <div class="flex items-center gap-2">
                <flux:input type="date" wire:model.live="dateFrom" class="w-40" />
                <span class="text-xs text-zinc-400">{{ __('to') }}</span>
                <flux:input type="date" wire:model.live="dateTo" class="w-40" />
            </div>
        </div>
        @endif

        <!-- Filtered Period Statistics Ribbon -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs">
            <div class="flex flex-wrap items-center gap-2 text-zinc-500">
                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                    {{ __('Showing :count transactions', ['count' => $this->filteredStats['count']]) }}
                </span>
                @if ($accountId)
                    @php $currAcc = $this->accounts->firstWhere('id', (int) $accountId); @endphp
                    <span>•</span>
                    <span class="inline-flex items-center gap-1 font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">
                        <span>{{ __('Account:') }}</span>
                        <strong>{{ $currAcc?->bank_name }}</strong>
                        <button type="button" wire:click="filterByAccount('')" class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 ml-0.5" title="{{ __('Clear account filter') }}">×</button>
                    </span>
                @endif
                @if ($categoryFilter)
                    <span>•</span>
                    <span class="inline-flex items-center gap-1 font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md">
                        <span>{{ __('Category:') }}</span>
                        <strong>{{ $categoryFilter }}</strong>
                        <button type="button" wire:click="$set('categoryFilter', '')" class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 ml-0.5">×</button>
                    </span>
                @endif
                @if ($typeFilter)
                    <span>•</span>
                    <span class="font-medium {{ $typeFilter === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ ucfirst($typeFilter) }}s only
                    </span>
                @endif
                @if ($dateFrom && $dateTo)
                    <span>•</span>
                    <span>{{ Carbon::parse($dateFrom)->format('d M') }} — {{ Carbon::parse($dateTo)->format('d M Y') }}</span>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <span class="text-zinc-500">{{ __('Period Credits:') }}</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">+₹{{ number_format($this->filteredStats['credits'], 2) }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-zinc-500">{{ __('Period Debits:') }}</span>
                    <span class="font-bold text-rose-600 dark:text-rose-400">-₹{{ number_format($this->filteredStats['debits'], 2) }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-zinc-500">{{ __('Net Period:') }}</span>
                    <span class="font-black {{ $this->filteredStats['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $this->filteredStats['net'] >= 0 ? '+' : '' }}₹{{ number_format($this->filteredStats['net'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </flux:card>

    <!-- Daily Passbook Ledger Table -->
    <div class="w-full overflow-x-auto rounded-2xl border border-zinc-200 dark:border-zinc-700/80 bg-white dark:bg-zinc-900 shadow-sm">
        <flux:table :paginate="$this->transactions">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Bank Account') }}</flux:table.column>
                <flux:table.column>{{ __('Party / Narration') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Ref / UTR #') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Credit (+)') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Debit (—)') }}</flux:table.column>
                <flux:table.column align="center">{{ __('Receipt') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->transactions as $tx)
                <flux:table.row class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/50 transition-colors">
                    <!-- Date -->
                    <flux:table.cell class="font-medium whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $tx->date->format('d M Y') }}
                            </span>
                            <span class="text-[11px] text-zinc-400">
                                {{ $tx->date->format('l') }}
                            </span>
                        </div>
                    </flux:table.cell>

                    <!-- Bank Account -->
                    <flux:table.cell class="whitespace-nowrap">
                        @if ($tx->account)
                            <button
                                type="button"
                                wire:click="filterByAccount('{{ $tx->account->id }}')"
                                class="flex items-center gap-2 text-start transition-transform hover:scale-105"
                                title="{{ __('Filter by :acc', ['acc' => $tx->account->bank_name]) }}"
                            >
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    <flux:icon name="building-library" class="size-3.5" />
                                </span>
                                <div>
                                    <span class="font-semibold text-xs text-zinc-900 dark:text-zinc-100 block">
                                        {{ $tx->account->bank_name }}
                                    </span>
                                    <span class="text-[10px] text-zinc-400 block">
                                        {{ $tx->account->maskedAccountNumber() }}
                                    </span>
                                </div>
                            </button>
                        @else
                            <span class="text-zinc-400 text-xs">—</span>
                        @endif
                    </flux:table.cell>

                    <!-- Party / Description -->
                    <flux:table.cell class="max-w-xs">
                        <div class="flex flex-col">
                            @if ($tx->party_name)
                                <span class="font-semibold text-xs text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $tx->party_name }}
                                </span>
                            @endif
                            @if ($tx->description)
                                <span class="text-xs text-zinc-500 truncate" title="{{ $tx->description }}">
                                    {{ $tx->description }}
                                </span>
                            @elseif (! $tx->party_name)
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <!-- Category -->
                    <flux:table.cell class="whitespace-nowrap">
                        <button
                            type="button"
                            wire:click="$set('categoryFilter', '{{ addslashes($tx->category) }}')"
                            class="transition-transform hover:scale-105 text-start"
                            title="{{ __('Filter by :cat', ['cat' => $tx->category]) }}"
                        >
                            <flux:badge size="sm" :color="$tx->type === 'credit' ? 'green' : 'zinc'" class="cursor-pointer hover:ring-1 hover:ring-indigo-400">
                                {{ $tx->category }}
                            </flux:badge>
                        </button>
                    </flux:table.cell>

                    <!-- Reference Number / UTR -->
                    <flux:table.cell class="whitespace-nowrap">
                        @if ($tx->reference_number)
                            <span class="font-mono text-xs font-medium text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700">
                                {{ $tx->reference_number }}
                            </span>
                        @else
                            <span class="text-zinc-400 text-xs">—</span>
                        @endif
                    </flux:table.cell>

                    <!-- Credit Amount (Inflow) -->
                    <flux:table.cell align="end" class="whitespace-nowrap">
                        @if ($tx->type === 'credit')
                            <span class="font-black text-sm text-emerald-600 dark:text-emerald-400">
                                +₹{{ number_format((float) $tx->amount, 2) }}
                            </span>
                        @else
                            <span class="text-zinc-300 dark:text-zinc-600">—</span>
                        @endif
                    </flux:table.cell>

                    <!-- Debit Amount (Outflow) -->
                    <flux:table.cell align="end" class="whitespace-nowrap">
                        @if ($tx->type === 'debit')
                            <span class="font-black text-sm text-rose-600 dark:text-rose-400">
                                -₹{{ number_format((float) $tx->amount, 2) }}
                            </span>
                        @else
                            <span class="text-zinc-300 dark:text-zinc-600">—</span>
                        @endif
                    </flux:table.cell>

                    <!-- Receipt Attachment -->
                    <flux:table.cell align="center" class="whitespace-nowrap">
                        @if ($tx->receipt_path)
                            <button
                                type="button"
                                wire:click="previewReceipt({{ $tx->id }})"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50 transition-colors"
                                title="{{ __('View Attached Receipt/Slip') }}"
                            >
                                <flux:icon name="document-text" class="size-3.5" />
                                <span>{{ __('View') }}</span>
                            </button>
                        @else
                            <span class="text-zinc-300 dark:text-zinc-600 text-xs">—</span>
                        @endif
                    </flux:table.cell>

                    <!-- Actions -->
                    <flux:table.cell align="end" class="whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1">
                            @can('update', $tx)
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil"
                                    wire:click="openTransactionModal('{{ $tx->type }}', {{ $tx->id }})"
                                    title="{{ __('Edit Entry') }}"
                                />
                            @endcan
                            @can('delete', $tx)
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="trash"
                                    wire:click="deleteTransaction({{ $tx->id }})"
                                    wire:confirm="{{ __('Delete this bank passbook entry? Account balance will be adjusted.') }}"
                                    title="{{ __('Delete Entry') }}"
                                    class="text-rose-500 hover:text-rose-700"
                                />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="9" class="text-center py-12 text-zinc-500">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                <flux:icon name="building-library" class="size-6" />
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-sm text-zinc-700 dark:text-zinc-300">{{ __('No bank records found') }}</span>
                                <span class="text-xs text-zinc-400">{{ __('Record a credit or debit entry using the action buttons above.') }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <flux:button size="sm" variant="primary" icon="arrow-down-left" wire:click="openTransactionModal('credit')">
                                    {{ __('+ Record Credit') }}
                                </flux:button>
                                <flux:button size="sm" variant="filled" icon="arrow-up-right" class="bg-rose-600 text-white" wire:click="openTransactionModal('debit')">
                                    {{ __('— Record Debit') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- ================= MODALS ================= -->

    <!-- Transaction Form Modal (Record Credit / Debit) -->
    <flux:modal wire:model.self="showTransactionModal" class="md:w-[560px]">
        <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg" class="font-bold">
                        @if ($editingTransactionId)
                            {{ __('Edit Passbook Entry') }}
                        @else
                            {{ $txType === 'credit' ? __('Record Credit (Money In)') : __('Record Debit (Money Out)') }}
                        @endif
                    </flux:heading>
                    <flux:subheading>
                        {{ __('Update your bank ledger with instant balance calculation.') }}
                    </flux:subheading>
                </div>

                <!-- Type Selector Pill -->
                <div class="inline-flex rounded-lg bg-zinc-100 dark:bg-zinc-800 p-0.5">
                    <button
                        type="button"
                        wire:click="$set('txType', 'credit')"
                        class="px-3 py-1 text-xs font-semibold rounded-md transition-colors {{ $txType === 'credit' ? 'bg-emerald-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400' }}"
                    >
                        {{ __('+ Credit') }}
                    </button>
                    <button
                        type="button"
                        wire:click="$set('txType', 'debit')"
                        class="px-3 py-1 text-xs font-semibold rounded-md transition-colors {{ $txType === 'debit' ? 'bg-rose-600 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400' }}"
                    >
                        {{ __('— Debit') }}
                    </button>
                </div>
            </div>

            <form wire:submit="saveTransaction" class="flex flex-col gap-4">
                <!-- Bank Account & Date -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="txAccountId" :label="__('Bank Account')" required>
                        @foreach ($this->accounts as $acc)
                            <flux:select.option value="{{ $acc->id }}">
                                {{ $acc->bank_name }} — {{ $acc->account_name }} (₹{{ number_format((float) $acc->current_balance, 0) }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input type="date" wire:model="txDate" :label="__('Date')" required />
                </div>

                <!-- Amount & Category -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:input
                            type="number"
                            step="0.01"
                            wire:model="txAmount"
                            :label="__('Amount (₹)')"
                            placeholder="0.00"
                            required
                        />
                    </div>

                    <flux:select wire:model="txCategory" :label="__('Category / Purpose')">
                        @if ($txType === 'credit')
                            <flux:select.option value="Customer Payment">📥 Customer Payment</flux:select.option>
                            <flux:select.option value="Cash Deposit">💵 Cash Deposit</flux:select.option>
                            <flux:select.option value="Owner Capital">💼 Owner Investment / Capital</flux:select.option>
                            <flux:select.option value="Loan Received">🏦 Loan / Financier Receipt</flux:select.option>
                            <flux:select.option value="Interest / Dividend">📈 Bank Interest / Returns</flux:select.option>
                            <flux:select.option value="Tax Refund">📑 Tax / GST Refund</flux:select.option>
                            <flux:select.option value="Rental Income">🏠 Rental Income</flux:select.option>
                            <flux:select.option value="Internal Transfer">⇄ Internal Transfer</flux:select.option>
                            <flux:select.option value="General Credit">🟢 General Credit</flux:select.option>
                        @else
                            <flux:select.option value="Vendor Payment">📤 Vendor / Supplier Payment</flux:select.option>
                            <flux:select.option value="Salary / Wages">👷 Salary / Labour Wages</flux:select.option>
                            <flux:select.option value="Cash Withdrawal">🏧 Cash / ATM Withdrawal</flux:select.option>
                            <flux:select.option value="Rent / Lease">🏢 Rent / Property Lease</flux:select.option>
                            <flux:select.option value="Utility / Electricity">⚡ Utility / Electricity Bill</flux:select.option>
                            <flux:select.option value="Owner Drawing">👤 Owner Drawing / Personal</flux:select.option>
                            <flux:select.option value="Loan EMI">🏦 Loan EMI / Interest Paid</flux:select.option>
                            <flux:select.option value="Taxes / GST">📑 Tax / GST Payment</flux:select.option>
                            <flux:select.option value="Bank Charges">💳 Bank Fees / Service Charges</flux:select.option>
                            <flux:select.option value="Internal Transfer">⇄ Internal Transfer</flux:select.option>
                            <flux:select.option value="General Debit">🔴 General Debit</flux:select.option>
                        @endif
                    </flux:select>
                </div>

                <!-- Party Name & Reference # -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="txPartyName"
                        :label="$txType === 'credit' ? __('Received From (Payer)') : __('Paid To (Payee)')"
                        placeholder="{{ __('e.g., Ansh Gohri, ABC Traders') }}"
                    />

                    <flux:input
                        wire:model="txReferenceNumber"
                        :label="__('Reference / UTR / Cheque #')"
                        placeholder="{{ __('e.g., UTR193821039, Cheque 40291') }}"
                    />
                </div>

                <!-- Narration / Description -->
                <flux:input
                    wire:model="txDescription"
                    :label="__('Narration / Notes')"
                    placeholder="{{ __('Short description of this transaction...') }}"
                />

                <!-- Receipt / Slip Attachment -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                        {{ __('Receipt / Bank Slip (Optional)') }}
                    </label>

                    @if ($existingReceiptPath)
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/50 dark:border-emerald-800 dark:bg-emerald-950/20">
                            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                                <flux:icon name="paper-clip" class="size-4" />
                                <span>{{ __('Current Receipt Attached') }}</span>
                            </div>
                            <flux:button size="xs" variant="ghost" class="text-rose-600" wire:click="removeExistingReceipt">
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    @endif

                    <input
                        type="file"
                        wire:model="txReceipt"
                        accept="image/*,application/pdf"
                        class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 dark:file:bg-zinc-800 file:text-zinc-700 dark:file:text-zinc-300 hover:file:bg-zinc-200 cursor-pointer"
                    />
                    <span class="text-[11px] text-zinc-400">{{ __('Supports JPG, PNG, WEBP, or PDF up to 10MB') }}</span>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button type="button" variant="ghost" wire:click="$set('showTransactionModal', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="check"
                        class="{{ $txType === 'credit' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white border-0"
                    >
                        {{ $editingTransactionId ? __('Update Entry') : ($txType === 'credit' ? __('Save Credit (+)') : __('Save Debit (—)')) }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Bank Account Management Modal (Add / Edit Account) -->
    <flux:modal wire:model.self="showAccountModal" class="md:w-[560px]">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg" class="font-bold">
                    {{ $editingAccountId ? __('Edit Bank Account') : __('Add Bank Account') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Configure your bank or cash ledger details.') }}
                </flux:subheading>
            </div>

            <form wire:submit="saveAccount" class="flex flex-col gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="accBankName"
                        :label="__('Bank / Institution Name')"
                        placeholder="{{ __('e.g., HDFC Bank, SBI, Cash Counter') }}"
                        required
                    />

                    <flux:input
                        wire:model="accAccountName"
                        :label="__('Account Label / Nickname')"
                        placeholder="{{ __('e.g., Main Current A/C, Reserve') }}"
                        required
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="accAccountNumber"
                        :label="__('Account Number')"
                        placeholder="{{ __('e.g., 50200012345678') }}"
                    />

                    <flux:input
                        wire:model="accIfscCode"
                        :label="__('IFSC Code')"
                        placeholder="{{ __('e.g., HDFC0001234') }}"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="accBranch"
                        :label="__('Branch Location')"
                        placeholder="{{ __('e.g., Connaught Place, Main Market') }}"
                    />

                    <flux:select wire:model="accColor" :label="__('Color Theme')">
                        <flux:select.option value="emerald">🟢 Emerald Green</flux:select.option>
                        <flux:select.option value="sky">🔵 Sky Blue</flux:select.option>
                        <flux:select.option value="indigo">🟣 Indigo Violet</flux:select.option>
                        <flux:select.option value="amber">🟠 Amber Gold</flux:select.option>
                        <flux:select.option value="rose">🔴 Rose Pink</flux:select.option>
                        <flux:select.option value="purple">🔮 Royal Purple</flux:select.option>
                        <flux:select.option value="teal">🌊 Deep Teal</flux:select.option>
                    </flux:select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        type="number"
                        step="0.01"
                        wire:model="accOpeningBalance"
                        :label="__('Opening Balance (₹)')"
                        placeholder="0.00"
                        required
                    />

                    <flux:input
                        type="date"
                        wire:model="accOpeningDate"
                        :label="__('Opening Date')"
                    />
                </div>

                <flux:input
                    wire:model="accNotes"
                    :label="__('Notes (Optional)')"
                    placeholder="{{ __('Any remarks about this account...') }}"
                />

                <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <div>
                        @if ($editingAccountId)
                            <flux:button
                                type="button"
                                variant="danger"
                                icon="trash"
                                wire:click="deleteAccount({{ $editingAccountId }})"
                                wire:confirm="{{ __('Delete this bank account? Only accounts with 0 transactions can be deleted.') }}"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <flux:button type="button" variant="ghost" wire:click="$set('showAccountModal', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit" variant="primary" icon="check">
                            {{ $editingAccountId ? __('Update Account') : __('Save Account') }}
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Internal Transfer Modal -->
    <flux:modal wire:model.self="showTransferModal" class="md:w-[500px]">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg" class="font-bold">
                    {{ __('Transfer Between Bank Accounts') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Automatically creates matching debit and credit entries.') }}
                </flux:subheading>
            </div>

            <form wire:submit="executeTransfer" class="flex flex-col gap-4">
                <flux:select wire:model="transferFromAccountId" :label="__('From Account (Debit)')" required>
                    @foreach ($this->accounts as $acc)
                        <flux:select.option value="{{ $acc->id }}">
                            {{ $acc->bank_name }} — {{ $acc->account_name }} (₹{{ number_format((float) $acc->current_balance, 0) }})
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="transferToAccountId" :label="__('To Account (Credit)')" required>
                    @foreach ($this->accounts as $acc)
                        <flux:select.option value="{{ $acc->id }}">
                            {{ $acc->bank_name }} — {{ $acc->account_name }} (₹{{ number_format((float) $acc->current_balance, 0) }})
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        type="number"
                        step="0.01"
                        wire:model="transferAmount"
                        :label="__('Transfer Amount (₹)')"
                        placeholder="0.00"
                        required
                    />

                    <flux:input
                        type="date"
                        wire:model="transferDate"
                        :label="__('Date')"
                        required
                    />
                </div>

                <flux:input
                    wire:model="transferReference"
                    :label="__('Transfer Ref / UTR #')"
                    placeholder="{{ __('e.g., NEFT/RTGS Reference') }}"
                />

                <flux:input
                    wire:model="transferNotes"
                    :label="__('Notes')"
                    placeholder="{{ __('Reason for transfer...') }}"
                />

                <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button type="button" variant="ghost" wire:click="$set('showTransferModal', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="arrows-right-left">
                        {{ __('Execute Transfer') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Receipt Preview Modal -->
    <flux:modal wire:model.self="showReceiptModal" class="md:w-[680px]">
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg" class="font-bold">{{ __('Attached Receipt Document') }}</flux:heading>
                    <flux:subheading>{{ $previewReceiptName }}</flux:subheading>
                </div>
                @if ($previewReceiptUrl)
                <a
                    href="{{ $previewReceiptUrl }}"
                    target="_blank"
                    download
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-colors"
                >
                    <flux:icon name="arrow-down-tray" class="size-4" />
                    <span>{{ __('Download Original') }}</span>
                </a>
                @endif
            </div>

            @if ($previewReceiptUrl)
                <div class="flex items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 p-2 overflow-hidden max-h-[70vh]">
                    @if (str_ends_with(strtolower($previewReceiptUrl), '.pdf'))
                        <iframe src="{{ $previewReceiptUrl }}" class="w-full h-96 rounded-lg border-0"></iframe>
                    @else
                        <img src="{{ $previewReceiptUrl }}" alt="Receipt" class="max-h-[65vh] w-auto rounded-lg object-contain" />
                    @endif
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button type="button" variant="ghost" wire:click="$set('showReceiptModal', false)">
                    {{ __('Close') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

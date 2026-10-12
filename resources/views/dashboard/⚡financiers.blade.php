<?php

use App\Enums\RoleName;
use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\Role;
use App\Models\User;
use App\Services\CacheService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Financiers')] class extends Component {
    use WithFileUploads, WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $payoutType = '';

    #[Url]
    public string $activeTab = 'financiers'; // financiers, payouts

    #[Url]
    public string $datePreset = 'today'; // today, yesterday, week, month, custom

    #[Url]
    public string $payoutDateFrom = '';

    #[Url]
    public string $payoutDateTo = '';

    public string $payoutSearch = '';
    public string $payoutTypeFilter = '';

    // Financier Modal State
    public bool $showFinancierModal = false;
    public ?int $editingFinancierId = null;
    public ?string $financier_user_id = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $payout_type = 'daily'; // daily, weekly, monthly
    public string $interest_type = 'interest_only'; // interest_only, principal_reducing
    public float $default_payment_amount = 0.0;
    public float $initial_loan_amount = 0.0;
    public string $status = 'active';
    public string $notes = '';

    // Payment Modal State
    public bool $showPaymentModal = false;
    public ?int $paymentFinancierId = null;
    public ?int $editingPaymentId = null;
    public string $type = 'daily_payment'; // daily_payment, weekly_payment, monthly_payment, loan_received, loan_repaid, interest_payment
    public float $amount = 0.0;
    public string $payment_method = 'cash';
    public string $payment_date = '';
    public string $payment_notes = '';
    public $bill_image = null;
    public ?string $existing_bill_path = null;

    // Ledger Modal State
    public bool $showLedgerModal = false;
    public ?int $ledgerFinancierId = null;
    public string $ledgerPeriod = 'this_month'; // this_month, this_week, this_year, all

    public function mount()
    {
        $this->authorize('viewAny', Financier::class);

        $user = Auth::user();
        if ($user->isFinancier()) {
            $financier = Financier::where('financier_user_id', $user->id)->first();
            if ($financier) {
                return $this->redirect(route('financiers.show', $financier->id), navigate: true);
            }
        }

        $this->payment_date = now()->toDateString();
        if (! $this->payoutDateFrom) {
            $this->payoutDateFrom = now()->startOfMonth()->toDateString();
        }
        if (! $this->payoutDateTo) {
            $this->payoutDateTo = now()->toDateString();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPayoutType(): void
    {
        $this->resetPage();
    }

    public function updatingPayoutSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPayoutTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDatePreset(): void
    {
        $this->resetPage();
    }

    public function setPreset(string $preset): void
    {
        $this->datePreset = $preset;
        $this->activeTab = 'payouts';

        if ($preset === 'custom') {
            if (! $this->payoutDateFrom) {
                $this->payoutDateFrom = now()->startOfMonth()->toDateString();
            }
            if (! $this->payoutDateTo) {
                $this->payoutDateTo = now()->toDateString();
            }
        }

        $this->resetPage();
    }

    #[Computed]
    public function dateRange(): array
    {
        return match ($this->datePreset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [
                $this->payoutDateFrom ? \Illuminate\Support\Carbon::parse($this->payoutDateFrom)->startOfDay() : now()->startOfMonth(),
                $this->payoutDateTo ? \Illuminate\Support\Carbon::parse($this->payoutDateTo)->endOfDay() : now()->endOfDay(),
            ],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    #[Computed]
    public function paymentsToday(): float
    {
        $user = Auth::user();

        return CacheService::rememberForUser('financiers.payments_today', $user->id, CacheService::STATS_TTL, function () use ($user) {
            return (float) FinancierPayment::query()
                ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
                ->when($user->isFinancier(), fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('financier_user_id', $user->id)))
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
                ->whereDate('date', today())
                ->sum('amount');
        });
    }

    #[Computed]
    public function paymentsYesterday(): float
    {
        $user = Auth::user();

        return CacheService::rememberForUser('financiers.payments_yesterday', $user->id, CacheService::STATS_TTL, function () use ($user) {
            return (float) FinancierPayment::query()
                ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
                ->when($user->isFinancier(), fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('financier_user_id', $user->id)))
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
                ->whereDate('date', now()->subDay())
                ->sum('amount');
        });
    }

    #[Computed]
    public function paymentsThisWeek(): float
    {
        $user = Auth::user();

        return CacheService::rememberForUser('financiers.payments_week', $user->id, CacheService::STATS_TTL, function () use ($user) {
            return (float) FinancierPayment::query()
                ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
                ->when($user->isFinancier(), fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('financier_user_id', $user->id)))
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
                ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('amount');
        });
    }

    #[Computed]
    public function paymentsThisMonth(): float
    {
        $user = Auth::user();

        return CacheService::rememberForUser('financiers.payments_month', $user->id, CacheService::STATS_TTL, function () use ($user) {
            return (float) FinancierPayment::query()
                ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
                ->when($user->isFinancier(), fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('financier_user_id', $user->id)))
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount');
        });
    }

    #[Computed]
    public function paymentsThisYear(): float
    {
        $user = Auth::user();

        return CacheService::rememberForUser('financiers.payments_year', $user->id, CacheService::STATS_TTL, function () use ($user) {
            return (float) FinancierPayment::query()
                ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
                ->when($user->isFinancier(), fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('financier_user_id', $user->id)))
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
                ->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])
                ->sum('amount');
        });
    }

    #[Computed]
    public function totalOutstandingBalance(): float
    {
        $user = Auth::user();

        return CacheService::rememberForUser('financiers.outstanding', $user->id, CacheService::STATS_TTL, function () use ($user) {
            return (float) Financier::query()
                ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
                ->when($user->isFinancier(), fn ($query) => $query->where('financier_user_id', $user->id))
                ->sum('outstanding_balance');
        });
    }

    #[Computed]
    public function financiers()
    {
        $user = Auth::user();

        return Financier::query()
            ->with(['payments', 'financierUser'])
            ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isFinancier(), fn ($query) => $query->where('financier_user_id', $user->id))
            ->when($this->payoutType, fn ($query) => $query->where('payout_type', $this->payoutType))
            ->when($this->search, fn ($query) => $query->where(function ($sub) {
                $sub->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                    ->orWhere('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                    ->orWhereHas('financierUser', fn ($q) => $q->where('email', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%"));
            }))
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

    #[Computed]
    public function payments()
    {
        if ($this->activeTab !== 'payouts') {
            return collect();
        }

        $user = Auth::user();
        [$startDate, $endDate] = $this->dateRange;

        return FinancierPayment::query()
            ->with('financier.financierUser', 'user')
            ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isFinancier(), fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('financier_user_id', $user->id)))
            ->when($this->payoutSearch, fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->payoutSearch}%")))
            ->when($this->payoutTypeFilter, fn ($query) => $query->where('type', $this->payoutTypeFilter))
            ->whereBetween('date', [$startDate, $endDate])
            ->latest('date')
            ->latest('id')
            ->paginate(15, ['*'], 'payoutsPage');
    }

    #[Computed]
    public function registeredUsers()
    {
        return User::query()
            ->with('role')
            ->where('status', '!=', 'disabled')
            ->where(function ($query) {
                $query->whereNull('email')
                    ->orWhere('email', 'not like', '%@khatabook.customer');
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedUser(): ?User
    {
        if (! $this->financier_user_id) {
            return null;
        }

        return User::with('role')->find($this->financier_user_id);
    }

    public function formatRoleName(?string $roleName): string
    {
        if (! $roleName) {
            return __('User');
        }

        return match ($roleName) {
            RoleName::SystemAdmin->value, 'System Admin' => __('System Admin'),
            RoleName::Admin->value, 'Admin' => __('Admin'),
            RoleName::Manager->value, 'Manager' => __('Manager'),
            RoleName::Staff->value, 'Staff' => __('Staff'),
            RoleName::Viewer->value, 'Viewer' => __('Viewer'),
            RoleName::Employee->value, 'ROLE_EMPLOYEE' => __('Employee'),
            RoleName::Financier->value, 'ROLE_FINANCIERS' => __('Financier'),
            RoleName::Customer->value, 'ROLE_CUSTOMER' => __('Customer'),
            RoleName::Supplier->value, 'ROLE_SUPPLIERS', 'ROLE_SUPPLIER' => __('Supplier'),
            default => str_starts_with($roleName, 'ROLE_') ? ucfirst(strtolower(substr($roleName, 5))) : $roleName,
        };
    }

    public function updatedFinancierUserId($value): void
    {
        if (! empty($value)) {
            $user = User::with('role')->find($value);
            if ($user) {
                $this->name = $user->name;
                $this->email = ! str_contains((string) $user->email, '@khatabook.customer') ? (string) $user->email : '';
                $this->phone = (string) ($user->phone ?? '');
            }
        }
    }

    public function clearUserSelection(): void
    {
        $this->financier_user_id = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
    }

    public function createFinancier(): void
    {
        $this->authorize('create', Financier::class);

        $this->reset(['editingFinancierId', 'financier_user_id', 'name', 'email', 'phone', 'default_payment_amount', 'initial_loan_amount', 'notes']);
        $this->payout_type = 'daily';
        $this->interest_type = 'interest_only';
        $this->status = 'active';
        $this->showFinancierModal = true;
    }

    public function editFinancier(int $id): void
    {
        $financier = Financier::with('financierUser')->findOrFail($id);
        $this->authorize('update', $financier);

        $this->editingFinancierId = $financier->id;
        $this->financier_user_id = $financier->financier_user_id ? (string) $financier->financier_user_id : null;
        $this->name = $financier->name;
        $this->email = (string) ($financier->financierUser?->email ?? '');
        $this->phone = (string) $financier->phone;
        $this->payout_type = $financier->payout_type;
        $this->interest_type = $financier->interest_type ?? 'interest_only';
        $this->default_payment_amount = (float) $financier->default_payment_amount;

        $initialLoanPayment = $financier->payments()->where('type', 'loan_received')->oldest()->first();
        $this->initial_loan_amount = $initialLoanPayment ? (float) $initialLoanPayment->amount : 0.0;

        $this->status = $financier->status;
        $this->notes = (string) $financier->notes;
        $this->showFinancierModal = true;
    }

    public function saveFinancier(): void
    {
        $existingFinancier = $this->editingFinancierId ? Financier::with('financierUser')->find($this->editingFinancierId) : null;
        $financierUserId = $this->financier_user_id ? (int) $this->financier_user_id : $existingFinancier?->financier_user_id;

        // Auto-match existing user by email if not explicitly selected from dropdown
        if (! $financierUserId && ! empty($this->email)) {
            $userWithEmail = User::where('email', $this->email)->first();
            if ($userWithEmail) {
                $financierUserId = $userWithEmail->id;
            }
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($financierUserId)],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'max:50'],
            'payout_type' => ['required', 'in:daily,weekly,monthly'],
            'interest_type' => ['required', 'in:interest_only,principal_reducing'],
            'default_payment_amount' => ['required', 'numeric', 'min:0'],
            'initial_loan_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ], [
            'phone.regex' => __('The phone number must contain only numbers.'),
        ]);

        $initialLoan = (float) ($validated['initial_loan_amount'] ?? 0.0);
        $email = $validated['email'] ?? null;
        unset($validated['initial_loan_amount'], $validated['email']);

        $generatedPassword = null;

        if ($financierUserId) {
            $linkedUser = User::find($financierUserId);
            if ($linkedUser) {
                // If user is exclusively a financier, keep their profile in sync
                if ($linkedUser->isFinancier()) {
                    $linkedUser->update([
                        'name' => $validated['name'],
                        'email' => $email ?: $linkedUser->email,
                        'phone' => $validated['phone'] ?? $linkedUser->phone,
                    ]);
                } elseif (! empty($validated['phone']) && empty($linkedUser->phone)) {
                    $linkedUser->update(['phone' => $validated['phone']]);
                }
            }
        } else {
            $financierRole = Role::firstOrCreate(
                ['name' => RoleName::Financier->value],
                ['description' => 'Financier account']
            );

            $nameParts = array_filter(explode(' ', trim($validated['name'])));
            $firstName = strtolower(reset($nameParts) ?: 'financier');
            $generatedPassword = $firstName . '@123';
            $generatedEmail = $email ?: 'fin_'.time().'_'.rand(1000, 9999).'@khatabook.financier';

            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $generatedEmail,
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($generatedPassword),
                'role_id' => $financierRole->id,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
            $financierUserId = $newUser->id;
        }

        if ($this->editingFinancierId) {
            $financier = Financier::findOrFail($this->editingFinancierId);
            $this->authorize('update', $financier);
            $financier->update([
                ...$validated,
                'financier_user_id' => $financierUserId,
            ]);

            $initialLoanPayment = $financier->payments()->where('type', 'loan_received')->oldest()->first();
            if ($initialLoanPayment) {
                if ($initialLoan > 0) {
                    $initialLoanPayment->update(['amount' => $initialLoan]);
                } else {
                    $initialLoanPayment->delete();
                }
            } elseif ($initialLoan > 0) {
                FinancierPayment::create([
                    'financier_id' => $financier->id,
                    'user_id' => Auth::id(),
                    'date' => $financier->created_at?->toDateString() ?? now()->toDateString(),
                    'type' => 'loan_received',
                    'amount' => $initialLoan,
                    'payment_method' => 'cash',
                    'notes' => __('Opening loan balance'),
                ]);
            }

            $financier->recalculateOutstandingBalance();
        } else {
            $this->authorize('create', Financier::class);
            $financier = Financier::create([
                ...$validated,
                'user_id' => Auth::id(),
                'financier_user_id' => $financierUserId,
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
                    'notes' => __('Opening loan balance'),
                ]);
            }
        }

        $this->showFinancierModal = false;
        unset($this->financiers);

        if ($generatedPassword) {
            Flux::toast(variant: 'success', text: __('Financier saved. Login created for :email with password: :password', ['email' => $email, 'password' => $generatedPassword]));
        } else {
            Flux::toast(variant: 'success', text: __('Financier saved successfully.'));
        }
    }

    public function openPaymentModal(int $financierId, ?string $defaultType = null): void
    {
        $financier = Financier::findOrFail($financierId);
        $this->authorize('update', $financier);

        $this->editingPaymentId = null;
        $this->existing_bill_path = null;
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
        $this->bill_image = null;
        $this->showPaymentModal = true;
    }

    public function editPayment(int $id): void
    {
        $payment = FinancierPayment::with('financier')->findOrFail($id);
        $this->authorize('update', $payment->financier);

        $this->editingPaymentId = $payment->id;
        $this->paymentFinancierId = $payment->financier_id;
        $this->type = $payment->type;
        $this->amount = (float) $payment->amount;
        $this->payment_method = $payment->payment_method;
        $this->payment_date = $payment->date->toDateString();
        $this->payment_notes = (string) $payment->notes;
        $this->bill_image = null;
        $this->existing_bill_path = $payment->bill_path;
        $this->showPaymentModal = true;
    }

    public function removeExistingBill(): void
    {
        if ($this->editingPaymentId) {
            $payment = FinancierPayment::with('financier')->find($this->editingPaymentId);
            if ($payment) {
                $this->authorize('update', $payment->financier);
                if ($payment->bill_path) {
                    if (Storage::exists($payment->bill_path)) {
                        Storage::delete($payment->bill_path);
                    }
                    $payment->update(['bill_path' => null]);
                    $this->existing_bill_path = null;
                    Flux::toast(variant: 'success', text: __('Receipt removed.'));
                }
            }
        }
    }

    public function savePayment(): void
    {
        $financier = Financier::findOrFail($this->paymentFinancierId);
        $this->authorize('update', $financier);

        if ($this->bill_image && method_exists($this->bill_image, 'exists') && ! $this->bill_image->exists()) {
            $this->addError('bill_image', __('The uploaded receipt file is no longer available on the server. Please select the file again.'));
            return;
        }

        $validated = $this->validate([
            'type' => ['required', 'in:daily_payment,weekly_payment,monthly_payment,loan_received,loan_repaid,interest_payment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,bank_transfer,cheque,other'],
            'payment_date' => ['required', 'date'],
            'payment_notes' => ['nullable', 'string'],
            'bill_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        if ($this->editingPaymentId) {
            $payment = FinancierPayment::findOrFail($this->editingPaymentId);
            $billPath = $payment->bill_path;
            if ($this->bill_image) {
                if ($billPath && Storage::exists($billPath)) {
                    Storage::delete($billPath);
                }
                $billPath = $this->bill_image->store('bills');
            }

            $payment->update([
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['payment_notes'],
                'bill_path' => $billPath,
            ]);

            $toastMsg = __('Financier payout updated successfully.');
        } else {
            $billPath = null;
            if ($this->bill_image) {
                $billPath = $this->bill_image->store('bills');
            }

            FinancierPayment::create([
                'financier_id' => $financier->id,
                'user_id' => Auth::id(),
                'date' => $validated['payment_date'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['payment_notes'],
                'bill_path' => $billPath,
            ]);

            $toastMsg = __('Financier payment recorded successfully.');
        }

        CacheService::invalidateUser(Auth::id());
        $this->showPaymentModal = false;
        $this->bill_image = null;
        $this->existing_bill_path = null;
        $this->editingPaymentId = null;
        unset($this->financiers);
        unset($this->selectedLedgerFinancier);
        unset($this->payments);
        $this->activeTab = 'payouts';
        Flux::toast(variant: 'success', text: $toastMsg);
    }
    
     public function deleteFinancier(int $id): void
    {
        $financier = Financier::findOrFail($id);
        $this->authorize('delete', $financier);

        $financier->delete();

        unset($this->financiers);
        Flux::toast(variant: 'success', text: __('Financier deleted successfully.'));
    }

    public function deletePayment(int $id): void
    {
        $payment = FinancierPayment::findOrFail($id);
        $this->authorize('delete', $payment->financier);

        $payment->delete();

        unset($this->payments);
        unset($this->financiers);
        Flux::toast(variant: 'success', text: __('Payment deleted successfully.'));
    }

    public function syncFinancierUser(int $financierId): void
    {
        $this->authorize('update', Financier::class);

        $financier = Financier::findOrFail($financierId);
        if ($financier->financier_user_id && $financier->financierUser) {
            Flux::toast(variant: 'info', text: __('Financier is already linked with user account ":name".', ['name' => $financier->financierUser->name]));
            return;
        }

        $financierRole = Role::firstOrCreate(
            ['name' => RoleName::Financier->value],
            ['description' => 'Financier account']
        );

        $existingUser = null;
        if (! empty($financier->phone)) {
            $existingUser = User::where('phone', $financier->phone)->first();
        }

        if (! $existingUser) {
            $nameParts = array_filter(explode(' ', trim($financier->name)));
            $firstName = strtolower(reset($nameParts) ?: 'financier');
            $generatedPassword = $firstName . '@123';
            $generatedEmail = 'fin_' . $financier->id . '_' . time() . '@khatabook.financier';

            $existingUser = User::create([
                'name' => $financier->name,
                'email' => $generatedEmail,
                'phone' => $financier->phone,
                'password' => Hash::make($generatedPassword),
                'role_id' => $financierRole->id,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }

        $financier->update(['financier_user_id' => $existingUser->id]);
        unset($this->financiers);
        Flux::toast(variant: 'success', text: __('Financier linked with user account ":name" successfully.', ['name' => $existingUser->name]));
    }

    public function syncAllFinanciersWithUsers(): void
    {
        $this->authorize('update', Financier::class);

        $unlinked = Financier::whereNull('financier_user_id')->get();
        if ($unlinked->isEmpty()) {
            Flux::toast(variant: 'info', text: __('All financiers are already linked with user accounts.'));
            return;
        }

        $financierRole = Role::firstOrCreate(
            ['name' => RoleName::Financier->value],
            ['description' => 'Financier account']
        );

        $count = 0;
        foreach ($unlinked as $financier) {
            $existingUser = null;
            if (! empty($financier->phone)) {
                $existingUser = User::where('phone', $financier->phone)->first();
            }

            if (! $existingUser) {
                $nameParts = array_filter(explode(' ', trim($financier->name)));
                $firstName = strtolower(reset($nameParts) ?: 'financier');
                $generatedPassword = $firstName . '@123';
                $generatedEmail = 'fin_' . $financier->id . '_' . time() . '_' . rand(100, 999) . '@khatabook.financier';

                $existingUser = User::create([
                    'name' => $financier->name,
                    'email' => $generatedEmail,
                    'phone' => $financier->phone,
                    'password' => Hash::make($generatedPassword),
                    'role_id' => $financierRole->id,
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);
            }

            $financier->update(['financier_user_id' => $existingUser->id]);
            $count++;
        }

        unset($this->financiers);
        Flux::toast(variant: 'success', text: __(':count financier(s) successfully synced with user accounts.', ['count' => $count]));
    }

    // Ledger Navigation
    public function viewLedger(int $financierId)
    {
        return $this->redirect(route('financiers.show', $financierId), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Financiers Ledger') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage daily & monthly financier payouts, loan amounts, and repayments.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            @can('update', App\Models\Financier::class)
            <flux:button variant="subtle" icon="arrow-path" wire:click="syncAllFinanciersWithUsers" title="{{ __('Link unlinked financiers to user accounts') }}">{{ __('Sync with Users') }}</flux:button>
            @endcan
            @can('create', App\Models\Financier::class)
            <flux:button variant="primary" icon="plus" wire:click="createFinancier">{{ __('Add Financier') }}</flux:button>
            @endcan
        </div>
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

    <!-- Tabs -->
    <div class="flex gap-4 border-b border-zinc-200 dark:border-zinc-700 mb-2">
        <button wire:click="$set('activeTab', 'financiers')" class="pb-2 px-1 text-sm font-medium transition-colors {{ $activeTab === 'financiers' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent' }}">
            {{ __('Financiers List') }}
        </button>
        <button wire:click="$set('activeTab', 'payouts')" class="pb-2 px-1 text-sm font-medium transition-colors {{ $activeTab === 'payouts' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-transparent' }}">
            {{ __('Payouts History') }}
        </button>
    </div>

    @if ($this->activeTab === 'financiers')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search financier name, phone or email...')" icon="magnifying-glass" />
        
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
                        <a href="{{ route('financiers.show', $financier->id) }}" wire:navigate class="hover:underline text-indigo-600 dark:text-indigo-400 font-semibold text-start">
                            {{ $financier->name }}
                        </a>
                        @if ($financier->financierUser)
                            <div class="text-xs text-zinc-500 font-normal mt-0.5 flex items-center gap-1.5 flex-wrap">
                                @if ($financier->financierUser->email && !str_contains($financier->financierUser->email, '@khatabook.customer'))
                                    <span>✉️ {{ $financier->financierUser->email }}</span>
                                @endif
                                <flux:badge size="xs" :color="match ($financier->financierUser->role?->name) { 'System Admin' => 'indigo', 'Admin' => 'purple', 'Manager' => 'blue', default => 'zinc' }">
                                    {{ $this->formatRoleName($financier->financierUser->role?->name) }}
                                </flux:badge>
                            </div>
                        @else
                            <div class="text-xs font-normal mt-1 flex items-center gap-1.5">
                                <flux:badge size="xs" color="amber">{{ __('No user linked') }}</flux:badge>
                                <flux:button size="xs" variant="subtle" icon="arrow-path" wire:click="syncFinancierUser({{ $financier->id }})">
                                    {{ __('Sync User') }}
                                </flux:button>
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $financier->phone ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col items-start gap-1">
                            <flux:badge :color="$financier->payout_type === 'daily' ? 'blue' : ($financier->payout_type === 'weekly' ? 'emerald' : 'purple')" size="sm">
                                {{ ucfirst($financier->payout_type) }}
                            </flux:badge>
                            <flux:badge :color="$financier->interest_type === 'interest_only' ? 'indigo' : 'sky'" size="sm">
                                {{ $financier->interest_type === 'interest_only' ? __('Interest Only') : __('Principal Reduction') }}
                            </flux:badge>
                        </div>
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
                            @can('update', $financier)
                            <flux:button size="sm" variant="subtle" icon="banknotes" wire:click="openPaymentModal({{ $financier->id }})">
                                {{ __('Pay Now') }}
                            </flux:button>
                            @endcan
                            <flux:button size="sm" variant="subtle" icon="eye" :href="route('financiers.show', $financier->id)" wire:navigate>{{ __('View Ledger') }}</flux:button>
                            @can('update', $financier)
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editFinancier({{ $financier->id }})" />
                            @endcan
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
    @endif
    
    @if ($this->activeTab === 'payouts')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
        <flux:input wire:model.live.debounce.400ms="payoutSearch" :placeholder="__('Search financier name...')" icon="magnifying-glass" />
        
        <flux:select wire:model.live="payoutTypeFilter" :placeholder="__('All Transaction Types')">
            <flux:select.option value="">{{ __('All Transactions') }}</flux:select.option>
            <flux:select.option value="daily_payment">{{ __('Daily Payment') }}</flux:select.option>
            <flux:select.option value="weekly_payment">{{ __('Weekly Payment') }}</flux:select.option>
            <flux:select.option value="monthly_payment">{{ __('Monthly Payment') }}</flux:select.option>
            <flux:select.option value="loan_received">{{ __('Loan Received') }}</flux:select.option>
            <flux:select.option value="loan_repaid">{{ __('Loan Repaid') }}</flux:select.option>
            <flux:select.option value="interest_payment">{{ __('Interest Paid') }}</flux:select.option>
        </flux:select>
        
        <flux:select wire:model.live="datePreset" :label="__('Date Range')">
            <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
            <flux:select.option value="yesterday">{{ __('Yesterday') }}</flux:select.option>
            <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
            <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Custom Range') }}</flux:select.option>
        </flux:select>
        
        @if ($datePreset === 'custom')
            <flux:input type="date" wire:model.live="payoutDateFrom" :label="__('From Date')" />
            <flux:input type="date" wire:model.live="payoutDateTo" :label="__('To Date')" />
        @endif
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->payments">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Financier') }}</flux:table.column>
                <flux:table.column>{{ __('Transaction Type') }}</flux:table.column>
                <flux:table.column>{{ __('Amount') }}</flux:table.column>
                <flux:table.column>{{ __('Payment Method') }}</flux:table.column>
                <flux:table.column>{{ __('Notes') }}</flux:table.column>
                <flux:table.column>{{ __('Recorded By') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->payments as $payment)
                <flux:table.row wire:key="payment-{{ $payment->id }}">
                    <flux:table.cell>{{ $payment->date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell class="font-medium">
                        <a href="{{ route('financiers.show', $payment->financier_id) }}" wire:navigate class="hover:underline text-indigo-600 dark:text-indigo-400">
                            {{ $payment->financier->name }}
                        </a>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match ($payment->type) { 'loan_received' => 'orange', 'loan_repaid' => 'blue', 'interest_payment' => 'purple', default => 'green' }" size="sm">
                            {{ ucwords(str_replace('_', ' ', $payment->type)) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="font-semibold {{ $payment->type === 'loan_received' ? 'text-orange-600 dark:text-orange-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $payment->type === 'loan_received' ? '+' : '-' }}₹{{ number_format($payment->amount, 2) }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="capitalize text-zinc-600 dark:text-zinc-400">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-xs text-zinc-500 max-w-xs truncate block" title="{{ $payment->notes }}">{{ $payment->notes ?: '-' }}</span>
                    </flux:table.cell>
                    <flux:table.cell>{{ $payment->user->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            @if ($payment->bill_path)
                            <flux:button size="sm" variant="subtle" icon="document" href="{{ Storage::url($payment->bill_path) }}" target="_blank">
                                {{ __('Bill') }}
                            </flux:button>
                            @endif
                            @can('update', $payment->financier)
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editPayment({{ $payment->id }})" title="{{ __('Edit payout') }}" />
                            @endcan
                            @can('delete', $payment->financier)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deletePayment({{ $payment->id }})" wire:confirm="{{ __('Delete this transaction?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-zinc-500 py-6">{{ __('No payout records found for the selected criteria.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
    @endif

    <!-- Add/Edit Financier Modal -->
    <flux:modal wire:model.self="showFinancierModal" class="md:w-[480px]">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ $editingFinancierId ? __('Edit Financier') : __('Add New Financier') }}</flux:heading>
                <flux:subheading size="sm">{{ __('Link an already registered system user (Admin, Manager, Staff) or enter new financier details manually.') }}</flux:subheading>
            </div>

            <form wire:submit="saveFinancier" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <flux:select wire:model.live="financier_user_id" :label="__('Registered User (Optional)')" searchable>
                        <flux:select.option value="">{{ __('-- Enter New Financier / Manual Entry --') }}</flux:select.option>
                        @foreach ($this->registeredUsers as $u)
                            <flux:select.option value="{{ $u->id }}">
                                {{ $u->name }} ({{ $this->formatRoleName($u->role?->name) }}){{ $u->phone ? " • {$u->phone}" : '' }}{{ (!str_contains((string) $u->email, '@khatabook.customer')) && $u->email ? " • {$u->email}" : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    @if ($this->selectedUser)
                        <div class="flex items-center justify-between p-2.5 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-lg border border-emerald-200 dark:border-emerald-800/60 text-xs">
                            <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-200">
                                <flux:icon name="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                                <span><strong>{{ __('Linked User:') }}</strong> {{ $this->selectedUser->name }} ({{ $this->formatRoleName($this->selectedUser->role?->name) }})</span>
                            </div>
                            <button type="button" wire:click="clearUserSelection" class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 underline font-medium">
                                {{ __('Clear / New') }}
                            </button>
                        </div>
                    @endif
                </div>

                <flux:input wire:model="name" :label="__('Financier / Company Name')" placeholder="e.g. Mahindra Finance" required />
                <flux:input type="email" wire:model="email" :label="__('Financier Email (Dashboard Login)')" placeholder="e.g. mahindra@example.com" />
                <flux:input wire:model="phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />
                
                <flux:select wire:model="payout_type" :label="__('Payout Frequency')" required>
                    <flux:select.option value="daily">{{ __('Daily Paid') }}</flux:select.option>
                    <flux:select.option value="weekly">{{ __('Weekly Paid') }}</flux:select.option>
                    <flux:select.option value="monthly">{{ __('Monthly Paid') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model="interest_type" :label="__('Loan Structure / EMI Mode')" required>
                    <flux:select.option value="interest_only">{{ __('Interest Only (EMI is interest, principal stays same)') }}</flux:select.option>
                    <flux:select.option value="principal_reducing">{{ __('Principal Reduction (EMI reduces loan balance)') }}</flux:select.option>
                </flux:select>

                <flux:input type="number" step="0.01" min="0" wire:model="default_payment_amount" :label="__('Default Payment Amount (₹)')" placeholder="e.g. 500 or 5000" required />
                
                <flux:input type="number" step="0.01" min="0" wire:model="initial_loan_amount" :label="__('Initial Loan Amount / Opening Balance (₹)')" placeholder="e.g. 100000 (optional)" />
                
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
    <flux:modal wire:model.self="showPaymentModal" class="md:w-[480px]">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ $editingPaymentId ? __('Edit Financier Payout / Entry') : __('Log Financier Payment / Loan') }}</flux:heading>
                @if ($paymentFinancierId)
                    @php $currentFin = \App\Models\Financier::find($paymentFinancierId); @endphp
                    @if ($currentFin)
                        <flux:subheading size="sm">{{ __('Financier:') }} <strong>{{ $currentFin->name }}</strong> ({{ ucfirst($currentFin->payout_type) }})</flux:subheading>
                    @endif
                @endif
            </div>

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

                @if ($existing_bill_path)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 text-xs border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-1.5 truncate">
                            <span>📄 {{ __('Existing Receipt:') }}</span>
                            <a href="{{ Storage::url($existing_bill_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 underline font-medium truncate">{{ basename($existing_bill_path) }}</a>
                        </div>
                        <button type="button" wire:click="removeExistingBill" class="text-rose-600 hover:text-rose-700 dark:text-rose-400 font-medium shrink-0 ml-2">
                            {{ __('Remove') }}
                        </button>
                    </div>
                @endif

                <flux:input type="file" wire:model="bill_image" :label="$existing_bill_path ? __('Replace Receipt Image (Optional)') : __('Bill / Receipt Image (Optional)')" accept="image/*,.pdf" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showPaymentModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingPaymentId ? __('Update Payout') : __('Save Payment') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

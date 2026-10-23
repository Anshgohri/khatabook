<?php

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SupplierPayment;
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

new #[Title('Suppliers')] class extends Component {
    use WithFileUploads, WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $activeTab = 'suppliers'; // suppliers, transactions

    #[Url]
    public string $datePreset = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public string $transactionSearch = '';
    public string $transactionTypeFilter = '';

    // Supplier Modal State
    public bool $showSupplierModal = false;
    public ?int $editingSupplierId = null;
    public ?string $supplier_user_id = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $location = '';
    public string $material_supplied = '';
    public float $initial_balance = 0.0;
    public string $status = 'active';
    public string $notes = '';

    // Payment/Purchase Modal State
    public bool $showPaymentModal = false;
    public ?int $paymentSupplierId = null;
    public ?int $editingPaymentId = null;
    public string $type = 'raw_material_purchase'; // raw_material_purchase, payment_made
    public float $amount = 0.0;
    public string $payment_method = 'cash';
    public string $payment_date = '';
    public string $payment_notes = '';
    public $bill_image = null;
    public ?string $existing_bill_path = null;

    // Ledger Modal State
    public bool $showLedgerModal = false;
    public ?int $ledgerSupplierId = null;
    public string $ledgerPeriod = 'this_month'; // this_month, this_week, this_year, all

    public function mount(): void
    {
        $this->authorize('viewAny', Supplier::class);
        $this->payment_date = now()->toDateString();
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
            $this->resetPage('transactionsPage');
        }
    }

    public function updatingTransactionSearch(): void
    {
        $this->resetPage('transactionsPage');
    }

    public function updatingTransactionTypeFilter(): void
    {
        $this->resetPage('transactionsPage');
    }

    public function updatedDatePreset(): void
    {
        switch ($this->datePreset) {
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
                // Do not change dates for custom
                break;
            default:
                $this->dateFrom = '';
                $this->dateTo = '';
                break;
        }
        $this->resetPage();
        $this->resetPage('transactionsPage');
    }

    #[Computed]
    public function payments()
    {
        $user = Auth::user();

        return SupplierPayment::query()
            ->with(['supplier', 'user'])
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->when($this->transactionSearch, fn ($query) => $query->where(function ($sub) {
                $sub->whereHas('supplier', function ($q) {
                    $q->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->transactionSearch}%")
                        ->orWhere('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->transactionSearch}%")
                        ->orWhere('location', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->transactionSearch}%")
                        ->orWhere('material_supplied', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->transactionSearch}%");
                })->orWhere('notes', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->transactionSearch}%")
                  ->orWhere('payment_method', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->transactionSearch}%");
            }))
            ->when($this->transactionTypeFilter, fn ($q) => $q->where('type', $this->transactionTypeFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->latest('date')
            ->latest('id')
            ->paginate(15, ['*'], 'transactionsPage');
    }

    #[Computed]
    public function filteredPurchases(): float
    {
        $user = Auth::user();
        return (float) SupplierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'raw_material_purchase')
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->sum('amount');
    }

    #[Computed]
    public function filteredPayments(): float
    {
        $user = Auth::user();
        return (float) SupplierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'payment_made')
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date', '<=', $this->dateTo))
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
            ->with(['payments' => function ($q) {
                $q->when($this->dateFrom, fn($query) => $query->whereDate('date', '>=', $this->dateFrom))
                  ->when($this->dateTo, fn($query) => $query->whereDate('date', '<=', $this->dateTo));
            }, 'supplierUser.role'])
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->when($this->search, fn ($query) => $query->where(function ($sub) {
                $sub->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                    ->orWhere('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                    ->orWhere('location', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                    ->orWhere('material_supplied', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                    ->orWhereHas('supplierUser', fn ($q) => $q->where('email', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%"));
            }))
            ->latest()
            ->paginate(15);
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
        if (! $this->supplier_user_id) {
            return null;
        }

        return User::with('role')->find($this->supplier_user_id);
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

    public function updatedSupplierUserId($value): void
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
        $this->supplier_user_id = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
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

        $this->reset(['editingSupplierId', 'supplier_user_id', 'name', 'email', 'phone', 'location', 'material_supplied', 'initial_balance', 'notes']);
        $this->status = 'active';
        $this->showSupplierModal = true;
    }

    public function editSupplier(int $id): void
    {
        $supplier = Supplier::with('supplierUser')->findOrFail($id);
        $this->authorize('update', $supplier);

        $this->editingSupplierId = $supplier->id;
        $this->supplier_user_id = $supplier->supplier_user_id ? (string) $supplier->supplier_user_id : null;
        $this->name = $supplier->name;
        $this->email = (string) ($supplier->supplierUser?->email ?? $supplier->email ?? '');
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
        $existingSupplier = $this->editingSupplierId ? Supplier::with('supplierUser')->find($this->editingSupplierId) : null;
        $supplierUserId = $this->supplier_user_id ? (int) $this->supplier_user_id : $existingSupplier?->supplier_user_id;

        // Auto-match existing user by email if not explicitly selected from dropdown
        if (! $supplierUserId && ! empty($this->email)) {
            $userWithEmail = User::where('email', $this->email)->first();
            if ($userWithEmail) {
                $supplierUserId = $userWithEmail->id;
            }
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($supplierUserId)],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'material_supplied' => ['nullable', 'string', 'max:255'],
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ], [
            'phone.regex' => __('The phone number must contain only numbers.'),
        ]);

        $initialBalance = (float) ($validated['initial_balance'] ?? 0.0);
        $email = $validated['email'] ?? null;
        unset($validated['initial_balance'], $validated['email']);

        $generatedPassword = null;

        if ($supplierUserId) {
            $linkedUser = User::find($supplierUserId);
            if ($linkedUser) {
                // If user is exclusively a supplier, keep their profile in sync
                if ($linkedUser->isSupplier()) {
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
            $supplierRole = Role::firstOrCreate(
                ['name' => RoleName::Supplier->value],
                ['description' => 'Supplier account']
            );

            $nameParts = array_filter(explode(' ', trim($validated['name'])));
            $firstName = strtolower(reset($nameParts) ?: 'supplier');
            $generatedPassword = $firstName . '@123';
            $generatedEmail = $email ?: 'sup_'.time().'_'.rand(1000, 9999).'@khatabook.supplier';

            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $generatedEmail,
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($generatedPassword),
                'role_id' => $supplierRole->id,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
            $supplierUserId = $newUser->id;
        }

        if ($this->editingSupplierId) {
            $supplier = Supplier::findOrFail($this->editingSupplierId);
            $this->authorize('update', $supplier);
            $supplier->update([
                ...$validated,
                'supplier_user_id' => $supplierUserId,
                'email' => $email,
            ]);
        } else {
            $this->authorize('create', Supplier::class);
            $supplier = Supplier::create([
                ...$validated,
                'user_id' => Auth::id(),
                'supplier_user_id' => $supplierUserId,
                'email' => $email,
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

        if ($generatedPassword) {
            Flux::toast(variant: 'success', text: __('Supplier saved. User created with password: :password', ['password' => $generatedPassword]));
        } else {
            Flux::toast(variant: 'success', text: __('Supplier saved successfully.'));
        }
    }

    public function syncSupplierUser(int $supplierId): void
    {
        $this->authorize('update', Supplier::class);

        $supplier = Supplier::findOrFail($supplierId);
        if ($supplier->supplier_user_id && $supplier->supplierUser) {
            Flux::toast(variant: 'info', text: __('Supplier is already linked with user account ":name".', ['name' => $supplier->supplierUser->name]));
            return;
        }

        $supplierRole = Role::firstOrCreate(
            ['name' => RoleName::Supplier->value],
            ['description' => 'Supplier account']
        );

        $existingUser = null;
        if (! empty($supplier->phone)) {
            $existingUser = User::where('phone', $supplier->phone)->first();
        }
        if (! $existingUser && ! empty($supplier->email)) {
            $existingUser = User::where('email', $supplier->email)->first();
        }

        if (! $existingUser) {
            $nameParts = array_filter(explode(' ', trim($supplier->name)));
            $firstName = strtolower(reset($nameParts) ?: 'supplier');
            $generatedPassword = $firstName . '@123';
            $generatedEmail = $supplier->email ?: 'sup_' . $supplier->id . '_' . time() . '@khatabook.supplier';

            $existingUser = User::create([
                'name' => $supplier->name,
                'email' => $generatedEmail,
                'phone' => $supplier->phone,
                'password' => Hash::make($generatedPassword),
                'role_id' => $supplierRole->id,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }

        $supplier->update([
            'supplier_user_id' => $existingUser->id,
            'email' => $existingUser->email && ! str_contains($existingUser->email, '@khatabook.supplier') ? $existingUser->email : $supplier->email,
        ]);
        unset($this->suppliers);
        Flux::toast(variant: 'success', text: __('Supplier linked with user account ":name" successfully.', ['name' => $existingUser->name]));
    }

    public function syncAllSuppliersWithUsers(): void
    {
        $this->authorize('update', Supplier::class);

        $unlinked = Supplier::whereNull('supplier_user_id')->get();
        if ($unlinked->isEmpty()) {
            Flux::toast(variant: 'info', text: __('All suppliers are already linked with user accounts.'));
            return;
        }

        $supplierRole = Role::firstOrCreate(
            ['name' => RoleName::Supplier->value],
            ['description' => 'Supplier account']
        );

        $count = 0;
        foreach ($unlinked as $supplier) {
            $existingUser = null;
            if (! empty($supplier->phone)) {
                $existingUser = User::where('phone', $supplier->phone)->first();
            }
            if (! $existingUser && ! empty($supplier->email)) {
                $existingUser = User::where('email', $supplier->email)->first();
            }

            if (! $existingUser) {
                $nameParts = array_filter(explode(' ', trim($supplier->name)));
                $firstName = strtolower(reset($nameParts) ?: 'supplier');
                $generatedPassword = $firstName . '@123';
                $generatedEmail = $supplier->email ?: 'sup_' . $supplier->id . '_' . time() . '_' . rand(100, 999) . '@khatabook.supplier';

                $existingUser = User::create([
                    'name' => $supplier->name,
                    'email' => $generatedEmail,
                    'phone' => $supplier->phone,
                    'password' => Hash::make($generatedPassword),
                    'role_id' => $supplierRole->id,
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);
            }

            $supplier->update([
                'supplier_user_id' => $existingUser->id,
                'email' => $existingUser->email && ! str_contains($existingUser->email, '@khatabook.supplier') ? $existingUser->email : $supplier->email,
            ]);
            $count++;
        }

        unset($this->suppliers);
        Flux::toast(variant: 'success', text: __(':count supplier(s) successfully synced with user accounts.', ['count' => $count]));
    }

    public function deleteSupplier(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->authorize('delete', $supplier);

        $supplier->delete();
        unset($this->suppliers);
        Flux::toast(variant: 'success', text: __('Supplier deleted successfully.'));
    }

    public function openPaymentModal(int $supplierId, string $defaultType = 'raw_material_purchase'): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $this->authorize('update', $supplier);

        $this->editingPaymentId = null;
        $this->existing_bill_path = null;
        $this->paymentSupplierId = $supplier->id;
        $this->type = $defaultType;
        $this->amount = 0.0;
        $this->payment_method = 'cash';
        $this->payment_date = now()->toDateString();
        $this->payment_notes = '';
        $this->bill_image = null;
        $this->showPaymentModal = true;
    }

    public function editPayment(int $id): void
    {
        $payment = SupplierPayment::with('supplier')->findOrFail($id);
        $this->authorize('update', $payment->supplier);

        $this->editingPaymentId = $payment->id;
        $this->paymentSupplierId = $payment->supplier_id;
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
            $payment = SupplierPayment::with('supplier')->find($this->editingPaymentId);
            if ($payment) {
                $this->authorize('update', $payment->supplier);
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

    public function deletePayment(int $id): void
    {
        $payment = SupplierPayment::with('supplier')->findOrFail($id);
        $this->authorize('update', $payment->supplier);

        if ($payment->bill_path && Storage::exists($payment->bill_path)) {
            Storage::delete($payment->bill_path);
        }

        $payment->delete();
        CacheService::invalidateUser(Auth::id());
        unset($this->suppliers, $this->payments, $this->selectedLedgerSupplier);
        Flux::toast(variant: 'success', text: __('Entry deleted.'));
    }

    public function savePayment(): void
    {
        $supplier = Supplier::findOrFail($this->paymentSupplierId);
        $this->authorize('update', $supplier);

        if ($this->bill_image && method_exists($this->bill_image, 'exists') && ! $this->bill_image->exists()) {
            $this->addError('bill_image', __('The uploaded receipt file is no longer available on the server. Please select the file again.'));
            return;
        }

        $validated = $this->validate([
            'type' => ['required', 'in:raw_material_purchase,payment_made'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,bank_transfer,cheque,other'],
            'payment_date' => ['required', 'date'],
            'payment_notes' => ['nullable', 'string'],
            'bill_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        if ($this->editingPaymentId) {
            $payment = SupplierPayment::where('supplier_id', $supplier->id)->findOrFail($this->editingPaymentId);
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

            $toastMsg = __('Supplier entry updated successfully.');
        } else {
            $billPath = null;
            if ($this->bill_image) {
                $billPath = $this->bill_image->store('bills');
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

            $toastMsg = __('Supplier entry recorded successfully.');
        }

        CacheService::invalidateUser(Auth::id());
        $this->showPaymentModal = false;
        $this->bill_image = null;
        $this->existing_bill_path = null;
        $this->editingPaymentId = null;
        unset($this->suppliers, $this->payments, $this->selectedLedgerSupplier);
        Flux::toast(variant: 'success', text: $toastMsg);
    }

    // Ledger Navigation
    public function viewLedger(int $supplierId)
    {
        return $this->redirect(route('suppliers.show', $supplierId), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Raw Material Suppliers') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage raw material vendors (e.g. Raja Assam for Baans, Suraj for Fatta), purchases, and balance ledgers.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="subtle" icon="arrow-path" wire:click="syncAllSuppliersWithUsers" title="{{ __('Link unlinked suppliers to user accounts') }}">{{ __('Sync with Users') }}</flux:button>
            @can('create', App\Models\Supplier::class)
            <flux:button variant="primary" icon="plus" wire:click="createSupplier">{{ __('Add Supplier') }}</flux:button>
            @endcan
        </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Purchases (Filtered)') }}</flux:text>
            <flux:heading size="lg">₹{{ number_format($this->filteredPurchases, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Paid to Suppliers (Filtered)') }}</flux:text>
            <flux:heading size="lg" class="text-green-600 dark:text-green-400">₹{{ number_format($this->filteredPayments, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Balance Owed to Suppliers') }}</flux:text>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">₹{{ number_format($this->totalOutstandingBalance, 2) }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search supplier name, location, or material...')" icon="magnifying-glass" />
        
        <flux:select wire:model.live="datePreset" :label="__('Date Range')">
            <flux:select.option value="">{{ __('All Time') }}</flux:select.option>
            <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
            <flux:select.option value="yesterday">{{ __('Yesterday') }}</flux:select.option>
            <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
            <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Custom Range') }}</flux:select.option>
        </flux:select>

        @if ($datePreset === 'custom')
            <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
            <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
        @endif
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->suppliers">
            <flux:table.columns>
                <flux:table.column>{{ __('Date Added') }}</flux:table.column>
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
                    <flux:table.cell class="whitespace-nowrap text-zinc-500">{{ $supplier->created_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell class="font-medium">
                        <a href="{{ route('suppliers.show', $supplier->id) }}" wire:navigate class="hover:underline text-indigo-600 dark:text-indigo-400 font-semibold text-start">
                            {{ $supplier->name }}
                        </a>
                        @if ($supplier->supplierUser)
                            <div class="text-xs text-zinc-500 font-normal mt-0.5 flex items-center gap-1.5 flex-wrap">
                                @if ($supplier->supplierUser->email && !str_contains($supplier->supplierUser->email, '@khatabook.supplier'))
                                    <span>✉️ {{ $supplier->supplierUser->email }}</span>
                                @endif
                                <flux:badge size="xs" :color="match ($supplier->supplierUser->role?->name) { 'System Admin' => 'indigo', 'Admin' => 'purple', 'Manager' => 'blue', default => 'zinc' }">
                                    {{ $this->formatRoleName($supplier->supplierUser->role?->name) }}
                                </flux:badge>
                            </div>
                        @else
                            <div class="text-xs font-normal mt-1 flex items-center gap-1.5">
                                <flux:badge size="xs" color="amber">{{ __('No user linked') }}</flux:badge>
                                <flux:button size="xs" variant="subtle" icon="arrow-path" wire:click="syncSupplierUser({{ $supplier->id }})">
                                    {{ __('Sync User') }}
                                </flux:button>
                            </div>
                        @endif
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
                            <flux:button size="sm" variant="subtle" icon="eye" :href="route('suppliers.show', $supplier->id)" wire:navigate>{{ __('View Ledger') }}</flux:button>
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editSupplier({{ $supplier->id }})" />
                            @can('delete', $supplier)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteSupplier({{ $supplier->id }})" wire:confirm="{{ __('Delete this supplier and all their records?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="10" class="text-center text-zinc-500 py-6">{{ __('No suppliers registered yet. Click "Add Supplier" to create one.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Add/Edit Supplier Modal -->
    <flux:modal wire:model.self="showSupplierModal" class="md:w-[480px]">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ $editingSupplierId ? __('Edit Supplier') : __('Add New Supplier') }}</flux:heading>
                <flux:subheading size="sm">{{ __('Link an already registered system user (Admin, Manager, Staff) or enter new supplier details manually.') }}</flux:subheading>
            </div>

            <form wire:submit="saveSupplier" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <flux:select wire:model.live="supplier_user_id" :label="__('Registered User (Optional)')" searchable>
                        <flux:select.option value="">{{ __('-- Enter New Supplier / Manual Entry --') }}</flux:select.option>
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

                <flux:input wire:model="name" :label="__('Supplier Name')" placeholder="e.g. Raja Assam" required />
                <flux:input type="email" wire:model="email" :label="__('Supplier Email (Dashboard Login)')" placeholder="e.g. raja@example.com" />
                <flux:input wire:model="phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />
                <flux:input wire:model="location" :label="__('Location / City')" placeholder="e.g. Assam" />
                <flux:input wire:model="material_supplied" :label="__('Material Supplied')" placeholder="e.g. Baans (Bamboo), Fatta" />

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

                <flux:textarea wire:model="payment_notes" :label="__('Notes / Item Details')" placeholder="e.g. 200 Baans purchased" rows="2" />

                <flux:input type="file" wire:model="bill_image" :label="__('Bill / Receipt Image (Optional)')" accept="image/*,.pdf" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showPaymentModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Entry') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

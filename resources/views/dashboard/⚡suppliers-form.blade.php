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
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Supplier Form')] class extends Component {
    public ?Supplier $supplier = null;
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

    public function mount(?Supplier $supplier = null): void
    {
        if ($supplier && $supplier->exists) {
            $this->authorize('update', $supplier);
            $supplier->load('supplierUser');

            $this->supplier = $supplier;
            $this->editingSupplierId = $supplier->id;
            $this->supplier_user_id = $supplier->supplier_user_id ? (string) $supplier->supplier_user_id : null;
            $this->name = $supplier->name;
            $this->email = (string) ($supplier->supplierUser?->email ?? $supplier->email ?? '');
            $this->phone = (string) ($supplier->phone ?? '');
            $this->location = (string) ($supplier->location ?? '');
            $this->material_supplied = (string) ($supplier->material_supplied ?? '');
            $this->initial_balance = 0.0;
            $this->status = $supplier->status ?? 'active';
            $this->notes = (string) ($supplier->notes ?? '');
        } else {
            $this->authorize('create', Supplier::class);
            $this->status = 'active';
            $this->initial_balance = 0.0;
        }
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

    public function save()
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

        CacheService::invalidateUser(Auth::id());

        if ($generatedPassword) {
            Flux::toast(variant: 'success', text: __('Supplier saved. User created with password: :password', ['password' => $generatedPassword]));
        } else {
            Flux::toast(variant: 'success', text: __('Supplier saved successfully.'));
        }

        $this->redirect(route('suppliers'), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto flex flex-col gap-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('suppliers')" wire:navigate />
        <div>
            <flux:heading size="xl">{{ $this->editingSupplierId ? __('Edit Supplier') : __('Add New Supplier') }}</flux:heading>
            <flux:subheading size="sm">
                {{ $this->editingSupplierId ? __('Update supplier information, contact details, and account status.') : __('Register a new vendor for raw material purchases, ledger tracking, and user account access.') }}
            </flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <!-- Account Linking Card -->
        <flux:card class="flex flex-col gap-4">
            <div>
                <flux:heading size="lg">{{ __('User Account Linking') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">
                    {{ __('Link this supplier to an existing system user, or leave empty to automatically create a dedicated login.') }}
                </flux:text>
            </div>

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
                    <div class="flex items-center justify-between p-3 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-lg border border-emerald-200 dark:border-emerald-800/60 text-xs">
                        <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-200">
                            <flux:icon name="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span><strong>{{ __('Linked User:') }}</strong> {{ $this->selectedUser->name }} ({{ $this->formatRoleName($this->selectedUser->role?->name) }})</span>
                        </div>
                        <button type="button" wire:click="clearUserSelection" class="text-xs text-rose-600 hover:text-rose-700 dark:text-rose-400 underline font-medium">
                            {{ __('Clear / New') }}
                        </button>
                    </div>
                @endif
            </div>
        </flux:card>

        <!-- Supplier Details Card -->
        <flux:card class="flex flex-col gap-4">
            <div>
                <flux:heading size="lg">{{ __('Supplier Information') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('Essential business and contact details.') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:input wire:model="name" :label="__('Supplier Name')" placeholder="e.g. Raja Assam" required />
                </div>

                <flux:input type="email" wire:model="email" :label="__('Supplier Email (Dashboard Login)')" placeholder="e.g. raja@example.com" />
                <flux:input wire:model="phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />

                <flux:input wire:model="location" :label="__('Location / City')" placeholder="e.g. Assam, Siliguri" />
                <flux:input wire:model="material_supplied" :label="__('Material Supplied')" placeholder="e.g. Baans (Bamboo), Fatta, Screws" />

                @if (! $this->editingSupplierId)
                    <div class="sm:col-span-2">
                        <flux:input type="number" step="0.01" min="0" wire:model="initial_balance" :label="__('Initial Opening Balance Owed (₹)')" placeholder="e.g. 40000 (optional)" />
                        <flux:text class="mt-1 text-xs text-zinc-500">
                            {{ __('If you already owe money to this supplier from prior purchases, enter it here to start the ledger balance.') }}
                        </flux:text>
                    </div>
                @endif

                <div class="sm:col-span-2">
                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="sm:col-span-2">
                    <flux:textarea wire:model="notes" :label="__('Notes / Remarks')" rows="3" placeholder="{{ __('Optional notes regarding supplier terms, delivery schedule, quality notes...') }}" />
                </div>
            </div>
        </flux:card>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" :href="route('suppliers')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $this->editingSupplierId ? __('Update Supplier') : __('Save Supplier') }}</span>
                <span wire:loading>{{ __('Saving...') }}</span>
            </flux:button>
        </div>
    </form>
</div>

<?php

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\CacheService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Supplier Transaction Form')] class extends Component {
    use WithFileUploads;

    public ?Supplier $supplier = null;
    public ?SupplierPayment $payment = null;
    public ?int $supplier_id = null;
    public ?int $editingPaymentId = null;

    public string $type = 'raw_material_purchase'; // raw_material_purchase, payment_made
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $payment_date = '';
    public string $payment_notes = '';
    public $bill_image = null;
    public ?string $existing_bill_path = null;

    public function mount($supplier = null, $payment = null): void
    {
        // Handle variations in parameter injection
        if ($supplier instanceof SupplierPayment && ! $payment) {
            $payment = $supplier;
            $supplier = $payment->supplier;
        }

        if (is_numeric($payment)) {
            $payment = SupplierPayment::with('supplier')->find($payment);
        }

        if (is_numeric($supplier)) {
            $supplier = Supplier::find($supplier);
        }

        if ($payment && $payment->exists) {
            $supplier = $supplier ?? $payment->supplier;
            $this->authorize('update', $supplier);

            $this->payment = $payment;
            $this->supplier = $supplier;
            $this->supplier_id = $supplier->id;
            $this->editingPaymentId = $payment->id;
            $this->type = $payment->type;
            $this->amount = (float) $payment->amount;
            $this->payment_method = $payment->payment_method;
            $this->payment_date = $payment->date ? $payment->date->toDateString() : now()->toDateString();
            $this->payment_notes = (string) ($payment->notes ?? '');
            $this->existing_bill_path = $payment->bill_path;
        } else {
            if ($supplier && $supplier->exists) {
                $this->authorize('update', $supplier);
                $this->supplier = $supplier;
                $this->supplier_id = $supplier->id;
            }

            $queryType = request()->query('type');
            if (in_array($queryType, ['raw_material_purchase', 'payment_made'], true)) {
                $this->type = $queryType;
            } else {
                $this->type = 'raw_material_purchase';
            }

            $this->payment_date = now()->toDateString();
            $this->payment_method = 'cash';
        }
    }

    #[Computed]
    public function suppliersList()
    {
        $user = Auth::user();

        return Supplier::query()
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function updatedSupplierId($value): void
    {
        if ($value) {
            $sup = Supplier::find($value);
            if ($sup) {
                $this->authorize('update', $sup);
                $this->supplier = $sup;
            }
        } else {
            $this->supplier = null;
        }
    }

    public function removeExistingBill(): void
    {
        if ($this->editingPaymentId && $this->supplier) {
            $this->authorize('update', $this->supplier);
            $payment = SupplierPayment::where('supplier_id', $this->supplier->id)->find($this->editingPaymentId);
            if ($payment && $payment->bill_path) {
                if (Storage::exists($payment->bill_path)) {
                    Storage::delete($payment->bill_path);
                }
                $payment->update(['bill_path' => null]);
                $this->existing_bill_path = null;
                Flux::toast(variant: 'success', text: __('Receipt removed.'));
            }
        }
    }

    public function save()
    {
        if (! $this->supplier && $this->supplier_id) {
            $this->supplier = Supplier::find($this->supplier_id);
        }

        if (! $this->supplier) {
            $this->addError('supplier_id', __('Please select a supplier.'));
            return;
        }

        $this->authorize('update', $this->supplier);

        if ($this->bill_image && method_exists($this->bill_image, 'exists') && ! $this->bill_image->exists()) {
            $this->addError('bill_image', __('The uploaded receipt file is no longer available on the server. Please select the file again.'));
            return;
        }

        $validated = $this->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'type' => ['required', 'in:raw_material_purchase,payment_made'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,upi,bank_transfer,cheque,other'],
            'payment_date' => ['required', 'date'],
            'payment_notes' => ['nullable', 'string'],
            'bill_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        if ($this->editingPaymentId) {
            $payment = SupplierPayment::where('supplier_id', $this->supplier->id)->findOrFail($this->editingPaymentId);
            $billPath = $payment->bill_path;
            if ($this->bill_image) {
                if ($billPath && Storage::exists($billPath)) {
                    Storage::delete($billPath);
                }
                $billPath = $this->bill_image->store('bills');
            }

            $payment->update([
                'supplier_id' => $this->supplier->id,
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
                'supplier_id' => $this->supplier->id,
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
        Flux::toast(variant: 'success', text: $toastMsg);

        $this->redirect(route('suppliers.show', $this->supplier->id), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="$this->supplier ? route('suppliers.show', $this->supplier->id) : route('suppliers')" wire:navigate />
        <div>
            <flux:heading size="xl">
                @if ($this->editingPaymentId)
                    {{ __('Edit Supplier Entry') }}
                @elseif ($this->type === 'payment_made')
                    {{ __('Record Payment to Supplier') }}
                @else
                    {{ __('Record Raw Material Purchase') }}
                @endif
            </flux:heading>
            <flux:subheading size="sm">
                {{ __('Manage supplier purchases, track incoming materials, and record payments against balances.') }}
            </flux:subheading>
        </div>
    </div>

    <!-- Supplier Overview Card -->
    @if ($this->supplier)
        <flux:card class="bg-gradient-to-br from-zinc-50 to-zinc-100/60 dark:from-zinc-900 dark:to-zinc-800/60 border border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold shrink-0">
                        {{ strtoupper(substr($this->supplier->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-base">{{ $this->supplier->name }}</span>
                            <flux:badge color="blue" size="sm">{{ $this->supplier->material_supplied ?? __('Raw Material') }}</flux:badge>
                        </div>
                        <div class="text-xs text-zinc-500 mt-0.5 flex items-center gap-3 flex-wrap">
                            @if ($this->supplier->location)
                                <span>📍 {{ $this->supplier->location }}</span>
                            @endif
                            @if ($this->supplier->phone)
                                <span>📞 {{ $this->supplier->phone }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-zinc-200 dark:border-zinc-700">
                    <flux:text size="xs">{{ __('Current Balance Owed') }}</flux:text>
                    <div>
                        @if ((float) $this->supplier->outstanding_balance > 0)
                            <flux:badge color="red" size="md">₹{{ number_format((float) $this->supplier->outstanding_balance, 2) }} due</flux:badge>
                        @else
                            <flux:badge color="zinc" size="md">₹0.00 Balance</flux:badge>
                        @endif
                    </div>
                </div>
            </div>
        </flux:card>
    @endif

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:card class="flex flex-col gap-5">
            <!-- If no supplier is locked in route, allow selection -->
            @if (! $this->supplier)
                <div>
                    <flux:select wire:model.live="supplier_id" :label="__('Supplier')" placeholder="{{ __('Select supplier...') }}" required>
                        <flux:select.option value="">{{ __('-- Select Supplier --') }}</flux:select.option>
                        @foreach ($this->suppliersList as $s)
                            <flux:select.option value="{{ $s->id }}">
                                {{ $s->name }} ({{ $s->material_supplied ?? __('Raw Material') }}){{ $s->location ? " • {$s->location}" : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            @else
                <input type="hidden" wire:model="supplier_id" value="{{ $this->supplier->id }}">
            @endif

            <!-- Transaction Type Selection -->
            <div class="flex flex-col gap-2">
                <flux:label>{{ __('Transaction Type') }}</flux:label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all {{ $this->type === 'raw_material_purchase' ? 'border-orange-500 bg-orange-50/60 dark:bg-orange-950/20 ring-1 ring-orange-500' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                📦 {{ __('Raw Material Purchase') }}
                            </span>
                            <flux:badge color="orange" size="xs">{{ __('Increases Debt (+)') }}</flux:badge>
                        </div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Bought raw material (e.g. Baans, Fatta). Increases balance owed to supplier.') }}
                        </span>
                        <input type="radio" wire:model.live="type" value="raw_material_purchase" class="sr-only">
                    </label>

                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all {{ $this->type === 'payment_made' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/20 ring-1 ring-emerald-500' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                💸 {{ __('Payment Made') }}
                            </span>
                            <flux:badge color="green" size="xs">{{ __('Reduces Debt (-)') }}</flux:badge>
                        </div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __('Money paid to the supplier via Cash, UPI, or Bank. Clears outstanding balance.') }}
                        </span>
                        <input type="radio" wire:model.live="type" value="payment_made" class="sr-only">
                    </label>
                </div>
            </div>

            <!-- Amount and Date -->
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input type="number" step="0.01" min="0.01" wire:model="amount" :label="__('Amount (₹)')" placeholder="e.g. 15000" required />

                <flux:input type="date" wire:model="payment_date" :label="__('Date')" required />
            </div>

            <!-- Payment Method -->
            <div>
                <flux:select wire:model="payment_method" :label="__('Payment Method')" required>
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="upi">{{ __('UPI / PhonePe / GPay / Paytm') }}</flux:select.option>
                    <flux:select.option value="bank_transfer">{{ __('Bank Transfer / NEFT / IMPS') }}</flux:select.option>
                    <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>
            </div>

            <!-- Notes / Item Details -->
            <div>
                <flux:textarea wire:model="payment_notes" :label="__('Notes / Item Details')" placeholder="{{ $this->type === 'raw_material_purchase' ? 'e.g. 200 Baans purchased, Truck #UP14-AB-1234' : 'e.g. Part payment transferred via Google Pay' }}" rows="3" />
            </div>

            <!-- Bill / Receipt Upload -->
            <div class="flex flex-col gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:label>{{ __('Bill / Receipt Document (Optional)') }}</flux:label>

                @if ($this->existing_bill_path)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 text-xs border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-2 truncate">
                            <span>📄</span>
                            <a href="{{ Storage::url($this->existing_bill_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 underline font-semibold truncate">
                                {{ basename($this->existing_bill_path) }}
                            </a>
                            <span class="text-zinc-400">({{ __('Current attachment') }})</span>
                        </div>
                        <button type="button" wire:click="removeExistingBill" class="text-rose-600 hover:text-rose-700 dark:text-rose-400 font-medium shrink-0 ml-3">
                            {{ __('Remove') }}
                        </button>
                    </div>
                @endif

                <flux:input type="file" wire:model="bill_image" :label="$this->existing_bill_path ? __('Replace Receipt Image / PDF') : null" accept="image/*,.pdf" />
                <div class="flex items-center justify-between text-xs text-zinc-500">
                    <span>{{ __('Supported formats: JPG, PNG, WEBP, PDF (Max 10MB)') }}</span>
                    <span wire:loading wire:target="bill_image" class="text-indigo-600 dark:text-indigo-400 font-medium">{{ __('Uploading...') }}</span>
                </div>
            </div>
        </flux:card>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" :href="$this->supplier ? route('suppliers.show', $this->supplier->id) : route('suppliers')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $this->editingPaymentId ? __('Update Entry') : __('Save Entry') }}</span>
                <span wire:loading>{{ __('Saving...') }}</span>
            </flux:button>
        </div>
    </form>
</div>

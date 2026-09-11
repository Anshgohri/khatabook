<?php

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sales Form')] class extends Component {
    public ?int $editingId = null;

    public string $date = '';

    public string $customer_id = '';

    public string $customer_name = '';

    public string $customer_phone = '';

    public string $customer_email = '';

    public string $customer_address = '';

    public string $customer_city = '';

    public $discount = 0;

    public string $payment_status = 'paid';

    public string $notes = '';

    /**
     * @var array<int, array{product_id: string|int, quantity: int, unit_price: float, total_price: float}>
     */
    public array $saleItems = [];

    public function mount(?Sale $sale = null): void
    {
        if ($sale && $sale->exists) {
            $this->authorize('update', $sale);
            $sale->load(['items.product', 'customer']);

            $this->editingId = $sale->id;
            $this->date = $sale->date->toDateString();
            $this->customer_id = $sale->customer_id ? (string) $sale->customer_id : '';
            $this->customer_name = $sale->customer_name;

            if ($sale->customer) {
                $this->customer_phone = $sale->customer->phone ?? '';
                $this->customer_email = str_contains((string) $sale->customer->email, '@khatabook.customer') ? '' : $sale->customer->email;
                $this->customer_address = $sale->customer->address ?? '';
                $this->customer_city = $sale->customer->city ?? '';
            }

            $this->discount = (float) ($sale->discount ?? 0);
            $this->payment_status = $sale->payment_status;
            $this->notes = (string) $sale->notes;

            if ($sale->items->isNotEmpty()) {
                $this->saleItems = $sale->items->map(fn($item) => [
                    'product_id' => $item->product_id ? (string) $item->product_id : '',
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ])->toArray();
            } else {
                $this->saleItems = [
                    [
                        'product_id' => '',
                        'quantity' => $sale->quantity,
                        'unit_price' => (float) $sale->unit_price,
                        'total_price' => (float) $sale->total_amount,
                    ],
                ];
            }
        } else {
            $this->authorize('create', Sale::class);

            $this->date = now()->toDateString();
            $this->discount = 0;
            $this->payment_status = 'paid';
            $this->saleItems = [
                ['product_id' => '', 'quantity' => 1, 'unit_price' => 0.0, 'total_price' => 0.0],
            ];
        }
    }

    public function updatedCustomerId($value): void
    {
        if (! empty($value)) {
            $cust = User::find($value);
            if ($cust) {
                $this->customer_name = $cust->name;
                $this->customer_phone = $cust->phone ?? '';
                $this->customer_email = str_contains((string) $cust->email, '@khatabook.customer') ? '' : $cust->email;
                $this->customer_address = $cust->address ?? '';
                $this->customer_city = $cust->city ?? '';
            }
        }
    }

    #[Computed]
    public function products()
    {
        return Product::query()->orderBy('name')->get();
    }

    #[Computed]
    public function existingCustomers()
    {
        return User::query()
            ->whereHas('role', fn($q) => $q->where('name', RoleName::Customer->value))
            ->orderBy('name')
            ->get();
    }

    public function addItem(): void
    {
        $this->saleItems[] = [
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0.0,
            'total_price' => 0.0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->saleItems[$index]);
        $this->saleItems = array_values($this->saleItems);
        if (empty($this->saleItems)) {
            $this->addItem();
        }
    }

    public function updatedSaleItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if ($field === 'product_id' && ! empty($value)) {
                $product = Product::find($value);
                if ($product) {
                    $this->saleItems[$index]['unit_price'] = (float) $product->unit_price;
                }
            }

            $qty = max(1, (int) ($this->saleItems[$index]['quantity'] ?? 1));
            $price = max(0, (float) ($this->saleItems[$index]['unit_price'] ?? 0));
            $this->saleItems[$index]['total_price'] = $qty * $price;
        }
    }

    #[Computed]
    public function subtotal(): float
    {
        return array_reduce($this->saleItems, function ($carry, $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            return $carry + ($qty * $price);
        }, 0.0);
    }

    #[Computed]
    public function netTotal(): float
    {
        $sub = $this->subtotal;
        $disc = min($sub, max(0, (float) ($this->discount ?? 0)));
        return max(0, $sub - $disc);
    }

    public function save()
    {
        $validated = $this->validate([
            'date' => ['required', 'date'],
            'customer_id' => ['nullable'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'customer_city' => ['nullable', 'string', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:paid,partial,unpaid'],
            'notes' => ['nullable', 'string'],
            'saleItems' => ['required', 'array', 'min:1'],
            'saleItems.*.product_id' => ['nullable'],
            'saleItems.*.quantity' => ['required', 'integer', 'min:1'],
            'saleItems.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $subtotal = $this->subtotal;
        $discountVal = (float) ($validated['discount'] ?? 0);

        if ($discountVal > $subtotal) {
            $this->addError('discount', __('Discount cannot be greater than subtotal amount (₹:max).', ['max' => number_format($subtotal, 2)]));
            return;
        }

        // Find or Create Customer User with ROLE_CUSTOMER
        $customerRole = Role::firstOrCreate(
            ['name' => RoleName::Customer->value],
            ['description' => 'Customer account for tracking customer sales and details.']
        );
        $customerUser = null;

        if (! empty($validated['customer_id'])) {
            $customerUser = User::find($validated['customer_id']);
        }

        if (! $customerUser && (! empty($validated['customer_email']) || ! empty($validated['customer_phone']))) {
            $customerUser = User::query()
                ->where(function ($q) use ($validated) {
                    if (! empty($validated['customer_email'])) {
                        $q->where('email', $validated['customer_email']);
                    }
                    if (! empty($validated['customer_phone'])) {
                        $q->orWhere('phone', $validated['customer_phone']);
                    }
                })
                ->first();
        }

        $emailToUse = ! empty($validated['customer_email'])
            ? $validated['customer_email']
            : ($customerUser?->email ?? 'cust_'.time().'_'.rand(1000, 9999).'@khatabook.customer');

        if ($customerUser) {
            $customerUser->update([
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?: $customerUser->phone,
                'address' => $validated['customer_address'] ?: $customerUser->address,
                'city' => $validated['customer_city'] ?: $customerUser->city,
                'role_id' => $customerRole?->id ?? $customerUser->role_id,
            ]);
        } else {
            $customerUser = User::create([
                'name' => $validated['customer_name'],
                'email' => $emailToUse,
                'phone' => $validated['customer_phone'] ?: null,
                'address' => $validated['customer_address'] ?: null,
                'city' => $validated['customer_city'] ?: null,
                'role_id' => $customerRole?->id,
                'password' => bcrypt(Str::random(16)),
                'status' => 'active',
            ]);
        }

        if ($this->editingId) {
            $sale = Sale::findOrFail($this->editingId);
            $this->authorize('update', $sale);
            $sale->update([
                'date' => $validated['date'],
                'customer_id' => $customerUser?->id,
                'customer_name' => $validated['customer_name'],
                'discount' => $discountVal,
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'],
            ]);
        } else {
            $this->authorize('create', Sale::class);
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'customer_id' => $customerUser?->id,
                'date' => $validated['date'],
                'customer_name' => $validated['customer_name'],
                'discount' => $discountVal,
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'],
                'items_sold' => 'Sale Items',
                'quantity' => 1,
                'unit_price' => 0,
                'total_amount' => 0,
            ]);
        }

        $sale->syncItemsAndInventory($validated['saleItems']);

        Flux::toast(variant: 'success', text: __('Sale saved successfully.'));

        return $this->redirect(route('sales'), navigate: true);
    }
}; ?>

<div class="max-w-6xl mx-auto flex flex-col gap-6 pb-12">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('sales') }}" wire:navigate>
                {{ __('Back to Sales') }}
            </flux:button>
            <flux:heading size="xl">{{ $editingId ? __('Edit Sale #') . $editingId : __('Create New Sale') }}</flux:heading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" href="{{ route('sales') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="save" icon="check">{{ __('Save Sale') }}</flux:button>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
            <flux:card class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Customer Details') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input type="date" wire:model="date" :label="__('Sale Date')" required />

                    <flux:select wire:model.live="customer_id" :label="__('Select Existing Customer')">
                        <flux:select.option value="">{{ __('-- Create New Customer / Manual --') }}</flux:select.option>
                        @foreach ($this->existingCustomers as $c)
                        <flux:select.option value="{{ $c->id }}">
                            {{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }} {{ $c->city ? "- {$c->city}" : '' }}
                        </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                    <flux:input wire:model="customer_name" :label="__('Customer Name')" :placeholder="__('e.g. Sumit Sharma')" required />
                    <flux:input wire:model="customer_phone" :label="__('Phone Number')" :placeholder="__('e.g. 9876543210')" />
                    <flux:input type="email" wire:model="customer_email" :label="__('Email Address')" :placeholder="__('e.g. customer@example.com')" />
                    <flux:input wire:model="customer_city" :label="__('City')" :placeholder="__('e.g. Jaipur / Delhi')" />
                </div>

                <flux:input wire:model="customer_address" :label="__('Full Address')" :placeholder="__('e.g. 123 Station Road, Jaipur')" />
            </flux:card>

            <flux:card class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Products & Line Items') }}</flux:heading>
                        <flux:subheading size="sm">{{ __('Add all products included in this sale transaction.') }}</flux:subheading>
                    </div>
                    <flux:button type="button" size="sm" variant="subtle" icon="plus" wire:click="addItem">
                        {{ __('Add Product Item') }}
                    </flux:button>
                </div>

                <div class="flex flex-col gap-3 pt-2">
                    @foreach ($saleItems as $index => $item)
                    <div class="grid grid-cols-12 gap-3 items-end p-3 bg-zinc-50 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm" wire:key="sale-item-row-{{ $index }}">
                        <div class="col-span-12 sm:col-span-5">
                            <flux:select wire:model.live="saleItems.{{ $index }}.product_id" :label="$index === 0 ? __('Product') : ''" :placeholder="__('Select product...')">
                                <flux:select.option value="">{{ __('-- Select Product --') }}</flux:select.option>
                                @foreach ($this->products as $p)
                                <flux:select.option value="{{ $p->id }}">
                                    {{ $p->name }} (Stock: {{ $p->stock_level }} {{ $p->unit }}) - ₹{{ number_format((float) $p->unit_price, 2) }}
                                </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <flux:input type="number" min="1" wire:model.live="saleItems.{{ $index }}.quantity" :label="$index === 0 ? __('Qty') : ''" required />
                        </div>

                        <div class="col-span-4 sm:col-span-3">
                            <flux:input type="number" step="0.01" min="0" wire:model.live="saleItems.{{ $index }}.unit_price" :label="$index === 0 ? __('Unit Price (₹)') : ''" required />
                        </div>

                        <div class="col-span-4 sm:col-span-2 flex items-center justify-between gap-1 pb-1">
                            <div class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                ₹{{ number_format(((int)($item['quantity'] ?? 1)) * ((float)($item['unit_price'] ?? 0)), 2) }}
                            </div>

                            @if (count($saleItems) > 1)
                            <flux:button type="button" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" wire:click="removeItem({{ $index }})" />
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </flux:card>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            <flux:card class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Payment & Financial Summary') }}</flux:heading>

                <div class="flex flex-col gap-3 py-2 border-y border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('Items Subtotal:') }}</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">₹{{ number_format($this->subtotal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <label class="text-sm font-medium text-amber-600 dark:text-amber-400">
                            {{ __('Discount (₹):') }}
                        </label>
                        <flux:input type="number" step="0.01" min="0" :max="$this->subtotal" wire:model.live="discount" class="w-32 text-right" placeholder="0.00" />
                    </div>
                </div>

                <div class="flex items-center justify-between py-1">
                    <span class="text-base font-bold text-zinc-800 dark:text-white">{{ __('Final Net Total:') }}</span>
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                        ₹{{ number_format($this->netTotal, 2) }}
                    </span>
                </div>

                <flux:select wire:model="payment_status" :label="__('Payment Status')">
                    <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                    <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
                    <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="notes" :label="__('Notes / Payment Comments')" rows="3" :placeholder="__('Optional notes...')" />

                <div class="pt-2">
                    <flux:button type="submit" variant="primary" class="w-full" size="lg" icon="check">
                        {{ __('Save Sale') }}
                    </flux:button>
                </div>
            </flux:card>
        </div>
    </form>
</div>

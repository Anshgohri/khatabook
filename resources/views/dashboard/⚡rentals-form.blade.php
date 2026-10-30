<?php

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public ?Rental $rental = null;

    public string $customer_name = '';
    public string $customer_phone = '';
    public ?int $customer_id = null;

    public string $rented_at = '';
    public string $returned_at = '';
    public string $status = 'active';
    public float $security_deposit = 0;
    public float $discount = 0;
    public float $total_rent = 0;
    public string $notes = '';

    public array $items = [];

    // Return & Settlement fields
    public string $settlement_type = 'even'; // 'refund_to_customer', 'collect_from_customer', 'even'
    public float $settlement_amount = 0;
    public string $payment_method = 'cash';
    public string $return_notes = '';
    public bool $restock_inventory = true;

    public function mount(Rental $rental = null): void
    {
        if ($rental && $rental->exists) {
            $this->rental = $rental;
            $this->customer_name = $rental->customer_name ?? '';
            $this->customer_phone = $rental->customer_phone ?? '';
            $this->customer_id = $rental->customer_id;
            $this->rented_at = $rental->rented_at?->format('Y-m-d') ?? now()->format('Y-m-d');
            $this->returned_at = $rental->returned_at?->format('Y-m-d') ?? '';
            $this->status = $rental->status;
            $this->security_deposit = (float) $rental->security_deposit;
            $this->discount = (float) $rental->discount;
            $this->total_rent = (float) $rental->total_rent;
            $this->notes = $rental->notes ?? '';

            $this->settlement_type = $rental->settlement_type ?? 'even';
            $this->settlement_amount = (float) ($rental->settlement_amount ?? 0);
            $this->payment_method = $rental->payment_method ?? 'cash';
            $this->return_notes = $rental->return_notes ?? '';
            $this->restock_inventory = $rental->restock_inventory ?? true;

            foreach ($rental->items as $item) {
                $this->items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'daily_rent' => (float) $item->daily_rent,
                    'returned_quantity' => (int) ($item->returned_quantity > 0 ? $item->returned_quantity : $item->quantity),
                ];
            }

            // If action query param requested direct return
            if (request()->query('action') === 'return' && $this->status !== 'returned') {
                $this->markAsReturning();
            } else {
                $this->calculateSettlement();
            }
        } else {
            $this->rented_at = now()->format('Y-m-d');
            $this->addItem();
        }
    }

    #[Computed]
    public function availableProducts()
    {
        return Product::orderBy('name')->get();
    }

    #[Computed]
    public function registeredCustomers()
    {
        return User::whereHas('role', function ($q) {
            $q->where('name', \App\Enums\RoleName::Customer);
        })->get();
    }

    #[Computed]
    public function daysCount(): int
    {
        if (empty($this->rented_at)) return 1;
        $start = Carbon::parse($this->rented_at)->startOfDay();
        $end = empty($this->returned_at) ? now()->startOfDay() : Carbon::parse($this->returned_at)->startOfDay();
        $days = (int) $start->diffInDays($end);
        return max(1, $days);
    }

    public function markAsReturning(): void
    {
        $this->status = 'returned';
        if (empty($this->returned_at)) {
            $this->returned_at = now()->format('Y-m-d');
        }
        $this->calculateRent();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'daily_rent' => 0,
            'returned_quantity' => 1,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateRent();
    }

    public function updatedCustomerId($value): void
    {
        if ($value) {
            $customer = User::find($value);
            if ($customer) {
                $this->customer_name = $customer->name ?? $this->customer_name;
                $this->customer_phone = $customer->phone ?? $this->customer_phone;
            }
        }
    }

    public function updated($name, $value): void
    {
        if (preg_match('/items\.(\d+)\.product_id/', $name, $matches)) {
            $index = $matches[1];
            if ($value) {
                $product = Product::find($value);
                if ($product && $product->rent_price) {
                    $this->items[$index]['daily_rent'] = (float) $product->rent_price;
                }
            }
        }

        if (preg_match('/items\.(\d+)\.quantity/', $name, $matches)) {
            $index = $matches[1];
            if (empty($this->items[$index]['returned_quantity'])) {
                $this->items[$index]['returned_quantity'] = (int) $value;
            }
        }
        
        $this->calculateRent();
    }

    public function updatedStatus(): void
    {
        if ($this->status === 'returned' && empty($this->returned_at)) {
            $this->returned_at = now()->format('Y-m-d');
        }
        $this->calculateRent();
    }
    
    public function updatedReturnedAt(): void
    {
        $this->calculateRent();
    }
    
    public function updatedRentedAt(): void
    {
        $this->calculateRent();
    }

    public function updatedDiscount(): void
    {
        $this->calculateRent();
    }

    public function updatedSecurityDeposit(): void
    {
        $this->calculateSettlement();
    }

    public function updatedTotalRent(): void
    {
        $this->calculateSettlement();
    }

    public function calculateRent(): void
    {
        if (empty($this->rented_at)) return;

        $start = Carbon::parse($this->rented_at)->startOfDay();
        $end = empty($this->returned_at) ? now()->startOfDay() : Carbon::parse($this->returned_at)->startOfDay();
        
        $days = (int) $start->diffInDays($end);
        if ($days < 1) $days = 1; // Minimum 1 day rent

        $total = 0;
        foreach ($this->items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $rate = (float) ($item['daily_rent'] ?? 0);
            $total += ($qty * $rate * $days);
        }

        $total -= (float) $this->discount;
        if ($total < 0) $total = 0;

        $this->total_rent = round($total, 2);
        $this->calculateSettlement();
    }

    public function calculateSettlement(): void
    {
        $deposit = (float) $this->security_deposit;
        $rent = (float) $this->total_rent;

        $diff = $deposit - $rent;

        if ($diff > 0) {
            $this->settlement_type = 'refund_to_customer';
            $this->settlement_amount = round($diff, 2);
        } elseif ($diff < 0) {
            $this->settlement_type = 'collect_from_customer';
            $this->settlement_amount = round(abs($diff), 2);
        } else {
            $this->settlement_type = 'even';
            $this->settlement_amount = 0.00;
        }
    }

    public function save(): void
    {
        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'regex:/^[0-9]+$/', 'max:50'],
            'rented_at' => ['required', 'date'],
            'returned_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'returned', 'overdue'])],
            'security_deposit' => ['numeric', 'min:0'],
            'discount' => ['numeric', 'min:0'],
            'total_rent' => ['numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.daily_rent' => ['required', 'numeric', 'min:0'],
        ];

        if ($this->status === 'returned') {
            $rules['returned_at'] = ['required', 'date'];
            $rules['payment_method'] = ['required', 'string'];
            $rules['settlement_type'] = ['required', Rule::in(['refund_to_customer', 'collect_from_customer', 'even'])];
            $rules['settlement_amount'] = ['numeric', 'min:0'];
            $rules['return_notes'] = ['nullable', 'string'];
            $rules['restock_inventory'] = ['boolean'];
            $rules['items.*.returned_quantity'] = ['required', 'integer', 'min:0'];
        }

        $validated = $this->validate($rules, [
            'customer_phone.regex' => __('The phone number must contain only numbers.'),
        ]);

        $this->calculateSettlement();

        DB::transaction(function () use ($validated) {
            $wasAlreadyReturned = $this->rental && $this->rental->status === 'returned';

            $rentalData = [
                'customer_id' => $this->customer_id ?: null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'rented_at' => $validated['rented_at'],
                'returned_at' => $validated['returned_at'] ?: null,
                'status' => $validated['status'],
                'security_deposit' => $validated['security_deposit'],
                'discount' => $validated['discount'],
                'total_rent' => $validated['total_rent'],
                'notes' => $validated['notes'],
                'settlement_type' => $this->status === 'returned' ? $this->settlement_type : null,
                'settlement_amount' => $this->status === 'returned' ? $this->settlement_amount : 0,
                'payment_method' => $this->status === 'returned' ? $this->payment_method : null,
                'return_notes' => $this->status === 'returned' ? $this->return_notes : null,
                'restock_inventory' => $this->status === 'returned' ? $this->restock_inventory : true,
            ];

            if ($this->rental) {
                $this->rental->update($rentalData);
                $this->rental->items()->delete();
            } else {
                $rentalData['user_id'] = auth()->id();
                $this->rental = Rental::create($rentalData);
            }

            foreach ($validated['items'] as $itemData) {
                $returnedQty = $this->status === 'returned'
                    ? (int) ($itemData['returned_quantity'] ?? $itemData['quantity'])
                    : 0;

                $rentalItem = $this->rental->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'daily_rent' => $itemData['daily_rent'],
                    'returned_quantity' => $returnedQty,
                ]);

                // If marked as returned and restock is enabled, increment inventory stock
                if ($this->status === 'returned' && ! $wasAlreadyReturned && $this->restock_inventory && $rentalItem->product_id && $returnedQty > 0) {
                    $product = Product::find($rentalItem->product_id);
                    InventoryLog::create([
                        'product_id' => $rentalItem->product_id,
                        'quantity_change' => $returnedQty,
                        'transaction_type' => 'return',
                        'date' => $this->returned_at ?: now()->format('Y-m-d'),
                        'notes' => "Rental #{$this->rental->id} Returned: {$product?->name} (Restocked {$returnedQty} units)",
                    ]);
                }
            }
        });

        $msg = $this->status === 'returned'
            ? __('Rental return completed and financial settlement saved successfully.')
            : __('Rental saved successfully.');

        Flux::toast(variant: 'success', text: $msg);
        $this->redirect(route('rentals'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto flex flex-col gap-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('rentals')" wire:navigate />
        <div>
            <flux:heading size="xl">{{ $rental ? ($status === 'returned' ? __('Rental Return & Settlement') : __('Edit Rental')) : __('New Rental') }}</flux:heading>
            <flux:subheading size="sm">{{ __('Assign rental equipment to customers, track duration, and manage deposits & returns.') }}</flux:subheading>
        </div>
    </div>

    <!-- Quick Return Callout for Active Rentals -->
    @if ($rental && $status !== 'returned')
    <div class="p-4 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50/80 dark:bg-emerald-950/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                <flux:icon name="arrow-down-tray" class="size-5" />
            </div>
            <div>
                <div class="font-semibold text-emerald-900 dark:text-emerald-100 text-sm sm:text-base">
                    {{ __('Customer Returning Equipment?') }}
                </div>
                <div class="text-xs text-emerald-700 dark:text-emerald-300">
                    {{ __('Click below to calculate days rented, compare deposit with rent, and settle the return transaction.') }}
                </div>
            </div>
        </div>
        <flux:button variant="primary" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium shrink-0 w-full sm:w-auto" wire:click="markAsReturning">
            {{ __('Process Return & Settle') }}
        </flux:button>
    </div>
    @endif

    <form wire:submit="save" class="flex flex-col gap-6">
        <!-- Customer & Rental Details Card -->
        <flux:card>
            <div class="mb-4">
                <flux:heading size="lg">{{ __('Customer & Rental Details') }}</flux:heading>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @if ($this->registeredCustomers->isNotEmpty())
                <div class="sm:col-span-2">
                    <flux:select wire:model.live="customer_id" :label="__('Quick Select Existing Customer (Optional)')">
                        <flux:select.option value="">{{ __('-- Enter customer manually or select from registered customers --') }}</flux:select.option>
                        @foreach ($this->registeredCustomers as $cust)
                        <flux:select.option value="{{ $cust->id }}">
                            {{ $cust->name }} {{ $cust->phone ? '('.$cust->phone.')' : '' }}
                        </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                @endif

                <flux:input wire:model="customer_name" :label="__('Customer Name')" :placeholder="__('e.g. Ramesh Kumar')" required />
                <flux:input wire:model="customer_phone" :label="__('Customer Phone')" :placeholder="__('e.g. 9876543210')" />
                
                <flux:input type="date" wire:model.live="rented_at" :label="__('Rented At')" required />
                
                <flux:select wire:model.live="status" :label="__('Status')" required>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="returned">{{ __('Returned') }}</flux:select.option>
                    <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
                </flux:select>
                
                <flux:input type="number" step="0.01" wire:model.live="security_deposit" :label="__('Security Deposit (₹)')" placeholder="0.00" />
                <flux:input type="number" step="0.01" wire:model.live="discount" :label="__('Discount (₹)')" placeholder="0.00" />
            </div>
            
            <div class="mt-4">
                <flux:textarea wire:model="notes" :label="__('Notes / Terms')" :placeholder="__('Optional notes, condition of equipment, remarks...')" />
            </div>
        </flux:card>

        <!-- Equipment Return & Financial Settlement Card (Displayed when status is Returned) -->
        @if ($status === 'returned')
        <flux:card class="border-2 border-emerald-500/50 bg-gradient-to-br from-emerald-50/50 via-white to-zinc-50 dark:from-emerald-950/20 dark:via-zinc-900 dark:to-zinc-900 shadow-md">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-emerald-600 text-white shrink-0">
                        <flux:icon name="banknotes" class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100 font-bold">
                            {{ __('Equipment Return & Financial Settlement') }}
                        </flux:heading>
                        <flux:subheading size="sm">
                            {{ __('Verify items returned, calculate total rent, and settle security deposit balance.') }}
                        </flux:subheading>
                    </div>
                </div>
                <flux:badge color="green" size="lg">
                    {{ __('Return Mode') }}
                </flux:badge>
            </div>

            <!-- Return Date, Rental Duration & Payment Mode -->
            <div class="grid gap-4 sm:grid-cols-3 mb-5">
                <div>
                    <flux:input type="date" wire:model.live="returned_at" :label="__('Return Date')" required />
                </div>

                <div class="p-3 rounded-xl bg-zinc-100/80 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 flex flex-col justify-center">
                    <span class="text-xs text-zinc-500 font-medium">{{ __('Rental Duration') }}</span>
                    <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">
                        {{ $this->daysCount }} {{ __($this->daysCount === 1 ? 'day' : 'days') }}
                        <span class="text-xs text-zinc-500 font-normal block sm:inline">({{ \Carbon\Carbon::parse($rented_at)->format('d M') }} → {{ !empty($returned_at) ? \Carbon\Carbon::parse($returned_at)->format('d M Y') : now()->format('d M Y') }})</span>
                    </div>
                </div>

                <div>
                    <flux:select wire:model="payment_method" :label="__('Settlement Payment Mode')">
                        <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                        <flux:select.option value="upi">{{ __('UPI / Online') }}</flux:select.option>
                        <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                        <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <!-- Real-time Settlement Math Callout -->
            <div class="rounded-xl p-4 mb-5 border {{ $settlement_type === 'refund_to_customer' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-950 dark:text-emerald-100' : ($settlement_type === 'collect_from_customer' ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 text-amber-950 dark:text-amber-100' : 'bg-zinc-50 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200') }}">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 rounded-full {{ $settlement_type === 'refund_to_customer' ? 'bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-100' : ($settlement_type === 'collect_from_customer' ? 'bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-100' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200') }}">
                            @if($settlement_type === 'refund_to_customer')
                                <flux:icon name="arrow-up-right" class="size-6" />
                            @elseif($settlement_type === 'collect_from_customer')
                                <flux:icon name="arrow-down-left" class="size-6" />
                            @else
                                <flux:icon name="check" class="size-6" />
                            @endif
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold opacity-80">
                                {{ __('Financial Settlement Outcome') }}
                            </div>
                            <div class="text-xl font-extrabold mt-0.5">
                                @if($settlement_type === 'refund_to_customer')
                                    {{ __('Refund ₹:amount to Customer', ['amount' => number_format($settlement_amount, 2)]) }}
                                @elseif($settlement_type === 'collect_from_customer')
                                    {{ __('Collect ₹:amount from Customer', ['amount' => number_format($settlement_amount, 2)]) }}
                                @else
                                    {{ __('Fully Settled (Even ₹0.00 balance)') }}
                                @endif
                            </div>
                            <div class="text-xs opacity-90 mt-1">
                                @if($settlement_type === 'refund_to_customer')
                                    {{ __('Security deposit of ₹:dep exceeds rent of ₹:rent. Store refunds balance deposit of ₹:bal to customer.', ['dep' => number_format($security_deposit, 2), 'rent' => number_format($total_rent, 2), 'bal' => number_format($settlement_amount, 2)]) }}
                                @elseif($settlement_type === 'collect_from_customer')
                                    {{ __('Total rent of ₹:rent exceeds security deposit of ₹:dep. Customer pays remaining rent due of ₹:bal.', ['dep' => number_format($security_deposit, 2), 'rent' => number_format($total_rent, 2), 'bal' => number_format($settlement_amount, 2)]) }}
                                @else
                                    {{ __('Security deposit of ₹:dep matches total rent of ₹:rent exactly.', ['dep' => number_format($security_deposit, 2), 'rent' => number_format($total_rent, 2)]) }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-start md:self-center bg-white dark:bg-zinc-900 px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-xs">
                        <div class="text-right">
                            <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-semibold">{{ __('Deposit Held') }}</div>
                            <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200">₹{{ number_format($security_deposit, 2) }}</div>
                        </div>
                        <span class="text-zinc-400 font-bold px-1">-</span>
                        <div class="text-right">
                            <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-semibold">{{ __('Total Rent') }}</div>
                            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400">₹{{ number_format($total_rent, 2) }}</div>
                        </div>
                        <span class="text-zinc-400 font-bold px-1">=</span>
                        <div class="text-right">
                            <div class="text-[10px] text-zinc-400 uppercase tracking-wider font-semibold">{{ __('Settlement') }}</div>
                            <div class="text-base font-extrabold {{ $settlement_type === 'refund_to_customer' ? 'text-emerald-600 dark:text-emerald-400' : ($settlement_type === 'collect_from_customer' ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500') }}">
                                ₹{{ number_format($settlement_amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Returned Quantities & Inventory Restock -->
            <div class="mb-4">
                <div class="text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider mb-2">
                    {{ __('Returned Product Quantities') }}
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-900">
                    @foreach($items as $idx => $item)
                    @php
                        $product = $this->availableProducts->firstWhere('id', $item['product_id']);
                    @endphp
                    <div class="p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm" wire:key="return-item-{{ $idx }}">
                        <div class="flex items-center gap-2">
                            <flux:icon name="cube" class="size-4 text-zinc-400 shrink-0" />
                            <div>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $product?->name ?? __('Product #:id', ['id' => $item['product_id']]) }}</span>
                                <span class="text-xs text-zinc-400 ml-1">({{ __('Dispatched: :qty units', ['qty' => $item['quantity']]) }})</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-zinc-500 whitespace-nowrap">{{ __('Units Returned:') }}</span>
                            <flux:input type="number" wire:model="items.{{ $idx }}.returned_quantity" min="0" max="{{ $item['quantity'] }}" class="w-24 text-center" />
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <flux:checkbox wire:model="restock_inventory" :label="__('Automatically restock returned items into store inventory')" :description="__('When checked, warehouse stock levels will be incremented by the returned quantity upon completion.')" />
                </div>
            </div>

            <!-- Return / Condition Notes -->
            <div class="mt-4">
                <flux:textarea wire:model="return_notes" :label="__('Return & Condition Notes')" :placeholder="__('Notes about equipment condition, any damages, return remarks...')" />
            </div>
        </flux:card>
        @endif

        <!-- Rental Products Card -->
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <flux:heading size="lg">{{ __('Rental Products') }}</flux:heading>
                    <flux:subheading size="sm" class="hidden sm:block">{{ __('Select products and specify quantity and daily rates.') }}</flux:subheading>
                </div>
                <flux:button size="sm" variant="subtle" icon="plus" wire:click="addItem">{{ __('Add Item') }}</flux:button>
            </div>

            <div class="flex flex-col gap-3">
                @foreach ($items as $index => $item)
                <div class="p-3 sm:p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 shadow-xs" wire:key="item-{{ $index }}">
                    <!-- Mobile card top with item label and delete button -->
                    <div class="flex items-center justify-between sm:hidden pb-2 mb-2 border-b border-zinc-200 dark:border-zinc-700/60">
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Item #:num', ['num' => $index + 1]) }}</span>
                        @if (count($items) > 1)
                        <flux:button size="xs" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" wire:click="removeItem({{ $index }})" title="{{ __('Remove Item') }}">
                            {{ __('Remove') }}
                        </flux:button>
                        @endif
                    </div>

                    <div class="grid grid-cols-12 gap-3 items-end">
                        <div class="col-span-12 sm:col-span-6 md:col-span-5">
                            <flux:select wire:model.live="items.{{ $index }}.product_id" :label="__('Product')" :placeholder="__('Select product...')" required>
                                <flux:select.option value="">{{ __('-- Select Product --') }}</flux:select.option>
                                @foreach ($this->availableProducts as $product)
                                <flux:select.option value="{{ $product->id }}">
                                    {{ $product->name }}@if((float) $product->rent_price > 0) (₹{{ number_format((float) $product->rent_price, 2) }}/day)@endif
                                </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="col-span-6 sm:col-span-2">
                            <flux:input type="number" wire:model.live="items.{{ $index }}.quantity" :label="__('Qty')" min="1" required />
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <flux:input type="number" step="0.01" wire:model.live="items.{{ $index }}.daily_rent" :label="__('Daily Rent (₹)')" min="0" required />
                        </div>

                        <div class="hidden sm:flex sm:col-span-1 md:col-span-2 items-center justify-end pb-1">
                            @if (count($items) > 1)
                            <flux:button variant="danger" icon="trash" size="sm" wire:click="removeItem({{ $index }})" title="{{ __('Remove Item') }}" />
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <flux:button size="sm" variant="subtle" icon="plus" wire:click="addItem">{{ __('Add Another Product') }}</flux:button>
                    <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="calculateRent">{{ __('Recalculate Rent') }}</flux:button>
                </div>

                <div class="text-left sm:text-right p-3 sm:p-0 rounded-lg bg-zinc-100/70 dark:bg-zinc-800/50 sm:bg-transparent">
                    @if($discount > 0)
                        <flux:text class="text-zinc-500 line-through decoration-rose-500 text-sm mb-0.5">
                            {{ __('Subtotal: ₹') }}{{ number_format($total_rent + $discount, 2) }}
                        </flux:text>
                    @endif
                    <div class="text-xs text-zinc-500">
                        {{ __('Total Rent') }} ({{ $this->daysCount }} {{ __($this->daysCount === 1 ? 'day' : 'days') }})
                    </div>
                    <flux:heading size="xl" class="text-emerald-600 dark:text-emerald-400 font-bold">
                        ₹{{ number_format($total_rent, 2) }}
                    </flux:heading>
                </div>
            </div>
        </flux:card>

        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:button variant="ghost" :href="route('rentals')" wire:navigate>{{ __('Cancel') }}</flux:button>
            @if ($status === 'returned')
            <flux:button type="submit" variant="primary" icon="check-badge" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                {{ __('Complete Return & Settle') }}
            </flux:button>
            @else
            <flux:button type="submit" variant="primary" icon="check">{{ __('Save Rental') }}</flux:button>
            @endif
        </div>
    </form>
</div>

<?php

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

            foreach ($rental->items as $item) {
                $this->items[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'daily_rent' => (float) $item->daily_rent,
                ];
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

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
            'daily_rent' => 0,
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

    public function calculateRent(): void
    {
        if (empty($this->rented_at)) return;

        $start = Carbon::parse($this->rented_at);
        $end = empty($this->returned_at) ? now() : Carbon::parse($this->returned_at);
        
        $days = $start->diffInDays($end);
        if ($days < 1) $days = 1; // Minimum 1 day rent

        $total = 0;
        foreach ($this->items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $rate = (float) ($item['daily_rent'] ?? 0);
            $total += ($qty * $rate * $days);
        }

        $total -= (float) $this->discount;
        if ($total < 0) $total = 0;

        $this->total_rent = $total;
    }

    public function save(): void
    {
        $validated = $this->validate([
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
        ], [
            'customer_phone.regex' => __('The phone number must contain only numbers.'),
        ]);

        DB::transaction(function () use ($validated) {
            if ($this->rental) {
                $this->rental->update([
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
                ]);
                $this->rental->items()->delete(); // recreate items
            } else {
                $this->rental = Rental::create([
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
                    'user_id' => auth()->id(),
                ]);
            }

            foreach ($validated['items'] as $item) {
                $this->rental->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'daily_rent' => $item['daily_rent'],
                ]);
            }
        });

        Flux::toast(variant: 'success', text: __('Rental saved successfully.'));
        $this->redirect(route('rentals'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto flex flex-col gap-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('rentals')" wire:navigate />
        <div>
            <flux:heading size="xl">{{ $rental ? __('Edit Rental') : __('New Rental') }}</flux:heading>
            <flux:subheading size="sm">{{ __('Assign rental equipment to customers, track duration, and manage deposits.') }}</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
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
                
                @if($status === 'returned')
                    <flux:input type="date" wire:model.live="returned_at" :label="__('Returned At')" required />
                @endif
                
                <flux:input type="number" step="0.01" wire:model="security_deposit" :label="__('Security Deposit (₹)')" placeholder="0.00" />
                <flux:input type="number" step="0.01" wire:model.live="discount" :label="__('Discount (₹)')" placeholder="0.00" />
            </div>
            
            <div class="mt-4">
                <flux:textarea wire:model="notes" :label="__('Notes / Terms')" :placeholder="__('Optional notes, condition of equipment, remarks...')" />
            </div>
        </flux:card>

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
                    <div class="text-xs text-zinc-500">{{ __('Total Calculated Rent (based on days)') }}</div>
                    <flux:heading size="xl" class="text-emerald-600 dark:text-emerald-400 font-bold">
                        ₹{{ number_format($total_rent, 2) }}
                    </flux:heading>
                </div>
            </div>
        </flux:card>

        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:button variant="ghost" :href="route('rentals')" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary" icon="check">{{ __('Save Rental') }}</flux:button>
        </div>
    </form>
</div>

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
        <flux:heading size="xl">{{ $rental ? __('Edit Rental') : __('New Rental') }}</flux:heading>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:card>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="customer_name" :label="__('Customer Name')" required />
                <flux:input wire:model="customer_phone" :label="__('Customer Phone')" />
                
                <flux:input type="date" wire:model.live="rented_at" :label="__('Rented At')" required />
                
                <flux:select wire:model.live="status" :label="__('Status')" required>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="returned">{{ __('Returned') }}</flux:select.option>
                    <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
                </flux:select>
                
                @if($status === 'returned')
                    <flux:input type="date" wire:model.live="returned_at" :label="__('Returned At')" required />
                @endif
                
                <flux:input type="number" step="0.01" wire:model="security_deposit" :label="__('Security Deposit (₹)')" />
                <flux:input type="number" step="0.01" wire:model.live="discount" :label="__('Discount (₹)')" />
            </div>
            
            <div class="mt-4">
                <flux:textarea wire:model="notes" :label="__('Notes')" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">{{ __('Rental Items') }}</flux:heading>
                <flux:button size="sm" variant="subtle" icon="plus" wire:click="addItem">{{ __('Add Item') }}</flux:button>
            </div>

            <div class="flex flex-col gap-4">
                @foreach ($items as $index => $item)
                <div class="flex items-end gap-3 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50" wire:key="item-{{ $index }}">
                    <div class="flex-1">
                        <flux:select wire:model.live="items.{{ $index }}.product_id" :label="__('Product')" required searchable>
                            <flux:select.option value="" disabled>{{ __('Select product...') }}</flux:select.option>
                            @foreach ($this->availableProducts as $product)
                            <flux:select.option value="{{ $product->id }}">{{ $product->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="w-24">
                        <flux:input type="number" wire:model.live="items.{{ $index }}.quantity" :label="__('Qty')" min="1" required />
                    </div>

                    <div class="w-32">
                        <flux:input type="number" step="0.01" wire:model.live="items.{{ $index }}.daily_rent" :label="__('Daily Rent (₹)')" min="0" required />
                    </div>

                    @if (count($items) > 1)
                    <div class="pb-1">
                        <flux:button variant="danger" icon="trash" wire:click="removeItem({{ $index }})" />
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-end">
                <div class="text-right">
                    @if($discount > 0)
                        <flux:text class="text-zinc-500 line-through decoration-rose-500 mb-1">
                            {{ __('Subtotal: ₹') }}{{ number_format($total_rent + $discount, 2) }}
                        </flux:text>
                    @endif
                    <flux:text class="text-zinc-500 mb-1">{{ __('Total Calculated Rent (based on days)') }}</flux:text>
                    <flux:heading size="xl" class="text-emerald-600 dark:text-emerald-400">
                        ₹{{ number_format($total_rent, 2) }}
                    </flux:heading>
                    <div class="mt-2">
                        <flux:button size="sm" variant="subtle" wire:click="calculateRent">{{ __('Recalculate Rent') }}</flux:button>
                    </div>
                </div>
            </div>
        </flux:card>

        <div class="flex justify-end gap-3">
            <flux:button variant="ghost" :href="route('rentals')" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Save Rental') }}</flux:button>
        </div>
    </form>
</div>

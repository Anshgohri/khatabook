<?php

use App\Models\InventoryLog;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Product Details')] class extends Component {
    public Product $product;

    public bool $showStockForm = false;
    public int $quantity_change = 0;
    public string $transaction_type = 'purchase';
    public string $stock_date = '';
    public string $stock_notes = '';

    public function mount(Product $product): void
    {
        $this->authorize('view', $product);
        $this->product = $product->load(['category', 'inventoryLogs' => fn ($q) => $q->latest('date')->latest('id')->take(15)]);
    }

    public function adjustStock(): void
    {
        $this->authorize('create', InventoryLog::class);
        $this->quantity_change = 0;
        $this->transaction_type = 'purchase';
        $this->stock_date = now()->toDateString();
        $this->stock_notes = '';
        $this->showStockForm = true;
    }

    public function saveStockAdjustment(): void
    {
        $this->authorize('create', InventoryLog::class);

        $validated = $this->validate([
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'transaction_type' => ['required', 'in:purchase,sale,adjustment,return,waste'],
            'stock_date' => ['required', 'date'],
            'stock_notes' => ['nullable', 'string'],
        ]);

        InventoryLog::create([
            'product_id' => $this->product->id,
            'quantity_change' => $validated['quantity_change'],
            'transaction_type' => $validated['transaction_type'],
            'date' => $validated['stock_date'],
            'notes' => $validated['stock_notes'],
        ]);

        $this->showStockForm = false;
        $this->product->refresh();
        $this->product->load(['category', 'inventoryLogs' => fn ($q) => $q->latest('date')->latest('id')->take(15)]);
        Flux::toast(variant: 'success', text: __('Stock adjusted successfully.'));
    }
}; ?>

<div class="max-w-4xl mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('products')" wire:navigate>
                {{ __('Back to Products') }}
            </flux:button>
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl">{{ $this->product->name }}</flux:heading>
                    @if ($this->product->type === 'raw_material')
                        <flux:badge color="blue" size="sm">{{ __('Raw Material') }}</flux:badge>
                    @else
                        <flux:badge color="indigo" size="sm">{{ __('Finished Good') }}</flux:badge>
                    @endif
                </div>
                <flux:subheading size="sm">
                    {{ $this->product->category?->name ?? __('Uncategorized') }} • {{ __('Added: :date', ['date' => $this->product->created_at ? $this->product->created_at->format('d M Y') : 'N/A']) }}
                </flux:subheading>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('create', App\Models\InventoryLog::class)
            <flux:button variant="subtle" icon="archive-box" wire:click="adjustStock">
                {{ __('Adjust Stock') }}
            </flux:button>
            @endcan

            @can('update', $this->product)
            <flux:button variant="primary" icon="pencil" :href="route('products.edit', $this->product->id)" wire:navigate>
                {{ __('Edit Product') }}
            </flux:button>
            @endcan
        </div>
    </div>

    <!-- Product Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Image Card -->
        <div class="md:col-span-1">
            <flux:card class="flex flex-col items-center justify-center p-4">
                @if ($this->product->has_custom_image)
                    <img src="{{ $this->product->image_url }}" alt="{{ $this->product->name }}" class="w-full h-64 object-cover rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm" />
                @else
                    <div class="w-full h-64 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex flex-col items-center justify-center text-zinc-400 gap-2">
                        <span class="text-4xl">📦</span>
                        <span class="text-xs font-medium">{{ __('No Custom Image Uploaded') }}</span>
                    </div>
                @endif

                <div class="w-full mt-4 flex items-center justify-between text-xs text-zinc-500 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                    <span>{{ __('Unit Type:') }} <strong>{{ $this->product->unit ?? 'pcs' }}</strong></span>
                    <flux:badge :color="$this->product->stock_level <= 0 ? 'red' : ($this->product->stock_level <= 5 ? 'amber' : 'green')" size="sm">
                        {{ $this->product->stock_level <= 0 ? __('Out of Stock') : ($this->product->stock_level <= 5 ? __('Low Stock') : __('In Stock')) }}
                    </flux:badge>
                </div>
            </flux:card>
        </div>

        <!-- Info & Pricing Card -->
        <div class="md:col-span-2 flex flex-col gap-6">
            <!-- Key Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <flux:card class="flex flex-col gap-1">
                    <flux:text size="xs">{{ __('Selling Price') }}</flux:text>
                    <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">
                        ₹{{ number_format((float) $this->product->unit_price, 2) }}
                    </flux:heading>
                    <flux:text size="xs" class="text-zinc-400">{{ __('per :unit', ['unit' => $this->product->unit ?? 'pcs']) }}</flux:text>
                </flux:card>

                <flux:card class="flex flex-col gap-1">
                    <flux:text size="xs">{{ __('Daily Rent') }}</flux:text>
                    <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">
                        {{ $this->product->rent_price ? '₹'.number_format((float) $this->product->rent_price, 2) : '—' }}
                    </flux:heading>
                    <flux:text size="xs" class="text-zinc-400">{{ __('per day') }}</flux:text>
                </flux:card>

                <flux:card class="flex flex-col gap-1 col-span-2 sm:col-span-1">
                    <flux:text size="xs">{{ __('Current Stock') }}</flux:text>
                    <flux:heading size="lg" class="{{ $this->product->stock_level <= 0 ? 'text-red-600' : 'text-zinc-900 dark:text-zinc-100' }}">
                        {{ $this->product->stock_level }} <span class="text-sm font-normal text-zinc-500">{{ $this->product->unit ?? 'pcs' }}</span>
                    </flux:heading>
                    <flux:text size="xs" class="text-zinc-400">{{ __('available') }}</flux:text>
                </flux:card>
            </div>

            <!-- Details Card -->
            <flux:card class="flex flex-col gap-4">
                <flux:heading size="md">{{ __('Specifications') }}</flux:heading>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <flux:text size="xs">{{ __('Category') }}</flux:text>
                        <div class="font-semibold text-zinc-900 dark:text-zinc-100 mt-0.5">
                            {{ $this->product->category?->name ?? __('Uncategorized') }}
                        </div>
                    </div>

                    <div>
                        <flux:text size="xs">{{ __('Item Type') }}</flux:text>
                        <div class="font-semibold text-zinc-900 dark:text-zinc-100 mt-0.5">
                            {{ $this->product->type === 'raw_material' ? __('Raw Material (Input)') : __('Finished Good (Sales Item)') }}
                        </div>
                    </div>

                    @if (auth()->user()?->isSystemAdmin())
                    <div>
                        <flux:text size="xs">{{ __('Production / Cost Price (Admin Only)') }}</flux:text>
                        <div class="font-semibold text-amber-600 dark:text-amber-400 mt-0.5">
                            ₹{{ number_format((float) $this->product->cost_price, 2) }}
                        </div>
                    </div>
                    @endif

                    <div>
                        <flux:text size="xs">{{ __('Last Updated') }}</flux:text>
                        <div class="font-medium text-zinc-600 dark:text-zinc-400 mt-0.5">
                            {{ $this->product->updated_at ? $this->product->updated_at->format('d M Y, h:i A') : 'N/A' }}
                        </div>
                    </div>
                </div>

                @if ($this->product->description)
                    <div class="pt-3 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:text size="xs">{{ __('Description') }}</flux:text>
                        <div class="text-sm text-zinc-700 dark:text-zinc-300 mt-1 whitespace-pre-wrap leading-relaxed">
                            {{ $this->product->description }}
                        </div>
                    </div>
                @endif
            </flux:card>
        </div>
    </div>

    <!-- Inventory Stock Movement History -->
    <flux:card class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="md">{{ __('Recent Stock Movements') }}</flux:heading>
                <flux:text class="text-xs text-zinc-500">{{ __('Last 15 inventory adjustments, purchases, and sales.') }}</flux:text>
            </div>

            @can('create', App\Models\InventoryLog::class)
            <flux:button size="sm" variant="subtle" icon="archive-box" wire:click="adjustStock">
                {{ __('Adjust Stock') }}
            </flux:button>
            @endcan
        </div>

        <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500 uppercase">
                    <tr>
                        <th class="py-2.5 px-3">{{ __('Date') }}</th>
                        <th class="py-2.5 px-3">{{ __('Type') }}</th>
                        <th class="py-2.5 px-3">{{ __('Change') }}</th>
                        <th class="py-2.5 px-3">{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->product->inventoryLogs as $log)
                        <tr>
                            <td class="py-2.5 px-3 whitespace-nowrap text-zinc-600 dark:text-zinc-400">
                                {{ $log->date ? \Carbon\Carbon::parse($log->date)->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="py-2.5 px-3">
                                <flux:badge size="xs" :color="match ($log->transaction_type) { 'purchase' => 'green', 'sale' => 'indigo', 'waste' => 'red', 'return' => 'blue', default => 'zinc' }">
                                    {{ ucfirst($log->transaction_type) }}
                                </flux:badge>
                            </td>
                            <td class="py-2.5 px-3 font-semibold {{ $log->quantity_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }} {{ $this->product->unit ?? 'pcs' }}
                            </td>
                            <td class="py-2.5 px-3 text-xs text-zinc-500 max-w-xs truncate">
                                {{ $log->notes ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-zinc-400 text-xs">
                                {{ __('No stock movement history logged yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    <!-- Adjust Stock Modal -->
    <flux:modal wire:model.self="showStockForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Adjust stock') }} — {{ $this->product->name }}</flux:heading>

            <form wire:submit="saveStockAdjustment" class="flex flex-col gap-4">
                <flux:input type="number" wire:model="quantity_change" :label="__('Quantity change')" :description="__('Use a negative number to reduce stock (e.g. -5).')" required />

                <flux:select wire:model="transaction_type" :label="__('Transaction type')">
                    <flux:select.option value="purchase">{{ __('Purchase (Added stock)') }}</flux:select.option>
                    <flux:select.option value="sale">{{ __('Sale (Deducted stock)') }}</flux:select.option>
                    <flux:select.option value="adjustment">{{ __('Adjustment (Audit count)') }}</flux:select.option>
                    <flux:select.option value="return">{{ __('Return (Customer returned)') }}</flux:select.option>
                    <flux:select.option value="waste">{{ __('Waste / Damage (Discarded)') }}</flux:select.option>
                </flux:select>

                <flux:input type="date" wire:model="stock_date" :label="__('Date')" required />
                <flux:textarea wire:model="stock_notes" :label="__('Notes')" rows="2" placeholder="e.g. New stock delivery from Raja Assam" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showStockForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>

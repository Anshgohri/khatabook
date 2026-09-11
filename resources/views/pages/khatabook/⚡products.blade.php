<?php

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductCategory;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Products & Inventory')] class extends Component {
    use WithFileUploads, WithPagination;

    public bool $showProductForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $product_category_id = '';

    public float $unit_price = 0;

    public string $description = '';

    public $image = null;

    public bool $showStockForm = false;

    public ?int $adjustingProductId = null;

    public string $adjustingProductName = '';

    public int $quantity_change = 0;

    public string $transaction_type = 'purchase';

    public string $stock_date = '';

    public string $stock_notes = '';

    public string $type = 'finished_good';

    public string $unit = 'pcs';

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::query()->orderBy('name')->get();
    }

    #[Computed]
    public function products()
    {
        return Product::query()->with('category')->orderBy('name')->paginate(15);
    }

    public function createProduct(): void
    {
        $this->authorize('create', Product::class);

        $this->reset(['editingId', 'name', 'description', 'image']);
        $this->product_category_id = '';
        $this->unit_price = 0;
        $this->type = 'finished_good';
        $this->unit = 'pcs';
        $this->showProductForm = true;
    }

    public function editProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->authorize('update', $product);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->product_category_id = (string) $product->product_category_id;
        $this->unit_price = (float) $product->unit_price;
        $this->type = $product->type ?? 'finished_good';
        $this->unit = $product->unit ?? 'pcs';
        $this->description = (string) $product->description;
        $this->image = null;
        $this->showProductForm = true;
    }

    public function saveProduct(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:raw_material,finished_good'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $validated['product_category_id'] = $validated['product_category_id'] ?: null;

        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
            $validated['image_path'] = $imagePath;
        }

        unset($validated['image']);

        if ($validated['product_category_id']) {
            $cat = ProductCategory::find($validated['product_category_id']);
            if ($cat) {
                if (str_contains(strtolower($cat->name), 'raw')) {
                    $validated['type'] = 'raw_material';
                } elseif (str_contains(strtolower($cat->name), 'finished')) {
                    $validated['type'] = 'finished_good';
                }
            }
        }

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $this->authorize('update', $product);
            $product->update($validated);
        } else {
            $this->authorize('create', Product::class);
            Product::create($validated);
        }

        $this->showProductForm = false;
        $this->image = null;
        unset($this->products);
        Flux::toast(variant: 'success', text: __('Product saved.'));
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->authorize('delete', $product);

        $product->delete();
        unset($this->products);
        Flux::toast(variant: 'success', text: __('Product deleted.'));
    }

    public function adjustStock(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->authorize('create', InventoryLog::class);

        $this->adjustingProductId = $product->id;
        $this->adjustingProductName = $product->name;
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
            'product_id' => $this->adjustingProductId,
            'quantity_change' => $validated['quantity_change'],
            'transaction_type' => $validated['transaction_type'],
            'date' => $validated['stock_date'],
            'notes' => $validated['stock_notes'],
        ]);

        $this->showStockForm = false;
        unset($this->products);
        Flux::toast(variant: 'success', text: __('Stock adjusted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:heading size="xl">{{ __('Products & Inventory') }}</flux:heading>

        @can('create', Product::class)
        <flux:button variant="primary" icon="plus" wire:click="createProduct">{{ __('Add product') }}</flux:button>
        @endcan
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->products">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Unit price') }}</flux:table.column>
                <flux:table.column>{{ __('Stock level') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->products as $product)
                <flux:table.row wire:key="product-{{ $product->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            @if ($product->image_path)
                                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-9 h-9 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700 shrink-0" />
                            @else
                                <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-400 text-xs shrink-0 font-medium">📦</div>
                            @endif
                            <div class="flex flex-col">
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
                                @if ($product->description)
                                    <span class="text-xs text-zinc-400 max-w-xs truncate" title="{{ $product->description }}">{{ $product->description }}</span>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($product->type === 'raw_material')
                            <flux:badge color="blue" size="sm">Raw Material</flux:badge>
                        @else
                            <flux:badge color="indigo" size="sm">Finished Good</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $product->category?->name ?? __('Uncategorized') }}</flux:table.cell>
                    <flux:table.cell>₹{{ number_format((float) $product->unit_price, 2) }}</flux:cell>
                    <flux:table.cell>
                        <flux:badge :color="$product->stock_level <= 0 ? 'red' : 'zinc'" size="sm">{{ $product->stock_level }} {{ $product->unit ?? 'pcs' }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('create', App\Models\InventoryLog::class)
                            <flux:button size="sm" variant="ghost" icon="archive-box" wire:click="adjustStock({{ $product->id }})">{{ __('Adjust stock') }}</flux:button>
                            @endcan
                            @can('update', $product)
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editProduct({{ $product->id }})" />
                            @endcan
                            @can('delete', $product)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteProduct({{ $product->id }})" wire:confirm="{{ __('Delete this product?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-500">{{ __('No products found.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal wire:model.self="showProductForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingId ? __('Edit product') : __('Add product') }}</flux:heading>

            <form wire:submit="saveProduct" class="flex flex-col gap-4">
                <flux:input wire:model="name" :label="__('Name')" placeholder="e.g. Bans (Bamboo) or Finished Table" required />

                <flux:select wire:model="type" :label="__('Item Type')" required>
                    <flux:select.option value="finished_good">{{ __('Finished Good (Sales Item)') }}</flux:select.option>
                    <flux:select.option value="raw_material">{{ __('Raw Material (Input Material)') }}</flux:select.option>
                </flux:select>

                <flux:input wire:model="unit" :label="__('Unit of Measurement')" placeholder="e.g. pcs, bundle, feet, kg" required />

                <flux:select wire:model="product_category_id" :label="__('Category')" :placeholder="__('Uncategorized')">
                    @foreach ($this->categories as $category)
                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input type="number" step="0.01" min="0" wire:model="unit_price" :label="__('Unit price (₹)')" required />
                <flux:textarea wire:model="description" :label="__('Description')" rows="2" />

                <flux:input type="file" wire:model="image" :label="__('Product Image (Optional)')" accept="image/*" />

                @if ($image)
                    <div class="flex items-center gap-3 p-2 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800">
                        <img src="{{ $image->temporaryUrl() }}" class="w-12 h-12 object-cover rounded-md border border-zinc-200" />
                        <span class="text-xs text-zinc-500">{{ __('New Image Preview') }}</span>
                    </div>
                @elseif ($editingId && ($existingProduct = App\Models\Product::find($editingId))?->image_path)
                    <div class="flex items-center gap-3 p-2 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800">
                        <img src="{{ Storage::url($existingProduct->image_path) }}" class="w-12 h-12 object-cover rounded-md border border-zinc-200" />
                        <span class="text-xs text-zinc-500">{{ __('Current Product Image') }}</span>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showProductForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showStockForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Adjust stock') }} — {{ $adjustingProductName }}</flux:heading>

            <form wire:submit="saveStockAdjustment" class="flex flex-col gap-4">
                <flux:input type="number" wire:model="quantity_change" :label="__('Quantity change')" :description="__('Use a negative number to remove stock.')" required />

                <flux:select wire:model="transaction_type" :label="__('Transaction type')">
                    <flux:select.option value="purchase">{{ __('Purchase') }}</flux:select.option>
                    <flux:select.option value="sale">{{ __('Sale') }}</flux:select.option>
                    <flux:select.option value="adjustment">{{ __('Adjustment') }}</flux:select.option>
                    <flux:select.option value="return">{{ __('Return') }}</flux:select.option>
                    <flux:select.option value="waste">{{ __('Waste') }}</flux:select.option>
                </flux:select>

                <flux:input type="date" wire:model="stock_date" :label="__('Date')" required />
                <flux:textarea wire:model="stock_notes" :label="__('Notes')" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showStockForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
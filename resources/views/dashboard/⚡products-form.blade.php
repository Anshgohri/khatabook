<?php

use App\Models\Product;
use App\Models\ProductCategory;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Product Form')] class extends Component {
    use WithFileUploads;

    public ?Product $product = null;
    public ?int $editingId = null;

    public string $name = '';
    public ?string $product_category_id = null;
    public ?float $unit_price = 0;
    public ?float $rent_price = null;
    public ?float $cost_price = 0;
    public string $type = 'finished_good';
    public string $unit = 'pcs';
    public string $description = '';
    public $image = null;
    public ?string $existing_image_path = null;

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->authorize('update', $product);

            $this->product = $product;
            $this->editingId = $product->id;
            $this->name = $product->name;
            $this->product_category_id = $product->product_category_id ? (string) $product->product_category_id : null;
            $this->unit_price = (float) $product->unit_price;
            $this->rent_price = $product->rent_price ? (float) $product->rent_price : null;
            $this->cost_price = (float) $product->cost_price;
            $this->type = $product->type ?? 'finished_good';
            $this->unit = $product->unit ?? 'pcs';
            $this->description = (string) ($product->description ?? '');
            $this->existing_image_path = $product->image_path;
        } else {
            $this->authorize('create', Product::class);
            $this->type = 'finished_good';
            $this->unit = 'pcs';
            $this->unit_price = 0;
            $this->rent_price = null;
            $this->cost_price = 0;
        }
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::query()->orderBy('name')->get();
    }

    public function removeExistingImage(): void
    {
        if ($this->editingId && $this->product) {
            $this->authorize('update', $this->product);
            if ($this->product->image_path && Storage::exists($this->product->image_path)) {
                Storage::delete($this->product->image_path);
            }
            $this->product->update(['image_path' => null]);
            $this->existing_image_path = null;
            Flux::toast(variant: 'success', text: __('Image removed.'));
        }
    }

    public function save()
    {
        if ($this->image && method_exists($this->image, 'exists') && ! $this->image->exists()) {
            $this->addError('image', __('The uploaded image is no longer available on the server. Please select the file again.'));
            return;
        }

        $this->product_category_id = $this->product_category_id ?: null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'rent_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'type' => ['required', 'in:raw_material,finished_good'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $validated['product_category_id'] = $validated['product_category_id'] ?: null;
        $validated['cost_price'] = (float) ($validated['cost_price'] ?? 0);

        if ($this->image) {
            if ($this->editingId && $this->product?->image_path && Storage::exists($this->product->image_path)) {
                Storage::delete($this->product->image_path);
            }
            $validated['image_path'] = $this->image->store('products');
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

        Flux::toast(variant: 'success', text: __('Product saved successfully.'));
        $this->redirect(route('products'), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('products')" wire:navigate />
            <div>
                <flux:heading size="xl">{{ $this->editingId ? __('Edit Product') : __('Add New Product') }}</flux:heading>
                <flux:subheading size="sm">
                    {{ $this->editingId ? __('Update product specifications, prices, and inventory information.') : __('Create a new finished product or raw material catalog entry.') }}
                </flux:subheading>
            </div>
        </div>

        @if ($this->editingId && $this->product)
            <div class="flex items-center gap-2">
                <flux:button variant="subtle" icon="eye" :href="route('products.show', $this->product->id)" wire:navigate>
                    {{ __('View Product') }}
                </flux:button>
            </div>
        @endif
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <!-- Basic Information Card -->
        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ __('Product Details') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('Basic classification and unit settings.') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:input wire:model="name" :label="__('Product / Material Name')" placeholder="e.g. Baans 20ft or Bamboo Chair" required />
                </div>

                <div>
                    <flux:select wire:model.live="type" :label="__('Item Type')" required>
                        <flux:select.option value="finished_good">{{ __('Finished Good (Sales Item)') }}</flux:select.option>
                        <flux:select.option value="raw_material">{{ __('Raw Material (Input Material)') }}</flux:select.option>
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model="product_category_id" :label="__('Category')" placeholder="{{ __('Uncategorized') }}">
                        <flux:select.option value="">{{ __('-- Uncategorized --') }}</flux:select.option>
                        @foreach ($this->categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="sm:col-span-2">
                    <flux:input wire:model="unit" :label="__('Unit of Measurement')" placeholder="e.g. pcs, bundle, feet, kg" required />
                </div>
            </div>
        </flux:card>

        <!-- Pricing Card -->
        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ __('Pricing & Costing') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('Set sales rates, rental fees, and internal production costs.') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:input type="number" step="0.01" min="0" wire:model="unit_price" :label="__('Selling Unit Price (₹)')" placeholder="0.00" required />
                </div>

                <div>
                    <flux:input type="number" step="0.01" min="0" wire:model="rent_price" :label="__('Daily Rent Price (₹)')" placeholder="0.00 (optional)" :description="__('Optional daily rent rate when item is given on rental.')" />
                </div>

                @if (auth()->user()?->isSystemAdmin())
                    <div class="sm:col-span-2">
                        <flux:input type="number" step="0.01" min="0" wire:model="cost_price" :label="__('Production / Making Cost (₹)')" placeholder="0.00" :description="__('Total cost to manufacture/procure this item (Visible only to System Admins)')" />
                    </div>
                @endif
            </div>
        </flux:card>

        <!-- Description & Image Card -->
        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ __('Description & Media') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('Product overview and catalog photo.') }}</flux:text>
            </div>

            <div>
                <flux:textarea wire:model="description" :label="__('Description')" rows="3" placeholder="{{ __('Optional item description, dimensions, material grade, or usage notes...') }}" />
            </div>

            <div class="flex flex-col gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:label>{{ __('Product Image') }}</flux:label>

                @if ($image)
                    <div class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <img src="{{ $image->temporaryUrl() }}" class="w-16 h-16 object-cover rounded-lg border border-zinc-300 dark:border-zinc-600 shadow-sm" />
                        <div>
                            <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ __('New Image Ready to Save') }}</span>
                            <div class="text-xs text-zinc-500">{{ __('Will replace previous image upon saving') }}</div>
                        </div>
                    </div>
                @elseif ($this->existing_image_path)
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-3">
                            <img src="{{ Storage::url($this->existing_image_path) }}" class="w-16 h-16 object-cover rounded-lg border border-zinc-300 dark:border-zinc-600 shadow-sm" />
                            <div>
                                <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Current Image') }}</span>
                                <div class="text-xs text-zinc-500">{{ basename($this->existing_image_path) }}</div>
                            </div>
                        </div>
                        <button type="button" wire:click="removeExistingImage" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 underline">
                            {{ __('Remove Image') }}
                        </button>
                    </div>
                @endif

                <flux:input type="file" wire:model="image" :label="$this->existing_image_path ? __('Replace Image (Optional)') : null" accept="image/*" />
                <div class="flex items-center justify-between text-xs text-zinc-500">
                    <span>{{ __('Supported formats: JPG, PNG, WEBP (Max 5MB)') }}</span>
                    <span wire:loading wire:target="image" class="text-indigo-600 dark:text-indigo-400 font-medium">{{ __('Uploading image...') }}</span>
                </div>
            </div>
        </flux:card>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" :href="route('products')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $this->editingId ? __('Update Product') : __('Save Product') }}</span>
                <span wire:loading>{{ __('Saving...') }}</span>
            </flux:button>
        </div>
    </form>
</div>

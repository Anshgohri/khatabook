<?php

use App\Models\ProductCategory;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Product Categories')] class extends Component {
    use WithPagination;

    public bool $showCategoryForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', ProductCategory::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::query()
            ->withCount('products')
            ->when($this->search !== '', fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate(15);
    }

    public function createCategory(): void
    {
        $this->authorize('create', ProductCategory::class);

        $this->reset(['editingId', 'name']);
        $this->showCategoryForm = true;
    }

    public function editCategory(int $categoryId): void
    {
        $category = ProductCategory::findOrFail($categoryId);

        $this->authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->showCategoryForm = true;
    }

    public function saveCategory(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:product_categories,name,' . ($this->editingId ?? 'NULL') . ',id',
            ],
        ]);

        if ($this->editingId) {
            $category = ProductCategory::findOrFail($this->editingId);
            $this->authorize('update', $category);
            $category->update($validated);
        } else {
            $this->authorize('create', ProductCategory::class);
            ProductCategory::create($validated);
        }

        $this->showCategoryForm = false;
        unset($this->categories);
        Flux::toast(variant: 'success', text: __('Category saved successfully.'));
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = ProductCategory::findOrFail($categoryId);

        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            Flux::toast(variant: 'danger', text: __('Cannot delete category with associated products. Reassign or remove products first.'));
            return;
        }

        $category->delete();
        unset($this->categories);
        Flux::toast(variant: 'success', text: __('Category deleted successfully.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Product Categories') }}</flux:heading>
            <flux:subheading>{{ __('Manage item categories for Ashok Kumar Bans Store, Karnal') }}</flux:subheading>
        </div>

        @can('create', App\Models\ProductCategory::class)
        <flux:button variant="primary" icon="plus" wire:click="createCategory">{{ __('Add category') }}</flux:button>
        @endcan
    </div>

    <div class="flex items-center justify-between gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search categories...')" class="max-w-xs" />
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->categories">
            <flux:table.columns>
                <flux:table.column>{{ __('Category Name') }}</flux:table.column>
                <flux:table.column>{{ __('Total Products') }}</flux:table.column>
                <flux:table.column>{{ __('Created At') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->categories as $category)
                <flux:table.row wire:key="category-{{ $category->id }}">
                    <flux:table.cell class="font-medium text-zinc-900 dark:text-zinc-100">{{ $category->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$category->products_count > 0 ? 'emerald' : 'zinc'" size="sm">
                            {{ $category->products_count }} {{ __($category->products_count === 1 ? 'item' : 'items') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $category->created_at?->format('M d, Y') ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            @can('update', $category)
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editCategory({{ $category->id }})" title="{{ __('Edit') }}" />
                            @endcan
                            @can('delete', $category)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteCategory({{ $category->id }})" wire:confirm="{{ __('Are you sure you want to delete this category?') }}" title="{{ __('Delete') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center text-zinc-500 py-8">
                        {{ __('No categories found.') }}
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal wire:model.self="showCategoryForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingId ? __('Edit Category') : __('Add Category') }}</flux:heading>

            <form wire:submit="saveCategory" class="flex flex-col gap-4">
                <flux:input wire:model="name" :label="__('Category Name')" placeholder="{{ __('e.g. Raw Bamboo, Scaffolding Ghodi') }}" required autofocus />

                <div class="flex justify-end gap-2 mt-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showCategoryForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
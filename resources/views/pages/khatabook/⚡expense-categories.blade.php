<?php

use App\Models\ExpenseCategory;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Expense Categories')] class extends Component {
    use WithPagination;

    public bool $showCategoryModal = false;

    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $icon = '🛒';

    public string $color = 'emerald';

    public string $type = 'business'; // business, consumable, household, personal

    public string $description = '';

    public string $search = '';

    public string $filterType = '';

    public function mount(): void
    {
        // Allow all non-financier non-customer users to view, but only System Admin can modify
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return ExpenseCategory::query()
            ->withCount('expenses')
            ->withSum('expenses', 'amount')
            ->when($this->search !== '', fn($query) => $query->where('name', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%'))
            ->when($this->filterType !== '', fn($query) => $query->where('type', $this->filterType))
            ->orderBy('name')
            ->paginate(15);
    }

    public function openModal(?int $id = null): void
    {
        if (! auth()->user()?->isSystemAdmin()) {
            Flux::toast(variant: 'danger', text: __('Only System Admin can manage expense categories.'));
            return;
        }

        if ($id) {
            $cat = ExpenseCategory::findOrFail($id);
            $this->editingCategoryId = $cat->id;
            $this->name = $cat->name;
            $this->icon = $cat->icon ?? '🛒';
            $this->color = $cat->color ?? 'emerald';
            $this->type = $cat->type ?? 'business';
            $this->description = $cat->description ?? '';
        } else {
            $this->reset(['editingCategoryId', 'name', 'description']);
            $this->icon = '🛒';
            $this->color = 'emerald';
            $this->type = 'business';
        }

        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        if (! auth()->user()?->isSystemAdmin()) {
            Flux::toast(variant: 'danger', text: __('Only System Admin can manage expense categories.'));
            return;
        }

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:expense_categories,name,' . ($this->editingCategoryId ?? 'NULL') . ',id',
            ],
            'icon' => ['required', 'string'],
            'color' => ['required', 'string'],
            'type' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editingCategoryId) {
            $cat = ExpenseCategory::findOrFail($this->editingCategoryId);
            $cat->update($validated);
            Flux::toast(variant: 'success', text: __('Expense category updated successfully.'));
        } else {
            ExpenseCategory::create($validated);
            Flux::toast(variant: 'success', text: __('Dynamic expense category created successfully.'));
        }

        $this->showCategoryModal = false;
        $this->reset(['editingCategoryId', 'name', 'description']);
        unset($this->categories);
    }

    public function deleteCategory(int $categoryId): void
    {
        if (! auth()->user()?->isSystemAdmin()) {
            Flux::toast(variant: 'danger', text: __('Only System Admin can delete expense categories.'));
            return;
        }

        $category = ExpenseCategory::findOrFail($categoryId);

        if ($category->expenses()->exists()) {
            Flux::toast(variant: 'danger', text: __('Cannot delete category with associated expense entries. Reassign or delete expenses first.'));
            return;
        }

        $category->delete();
        unset($this->categories);
        Flux::toast(variant: 'success', text: __('Expense category deleted successfully.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">{{ __('Expense Categories') }}</flux:heading>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/40">
                    📂 {{ __('Dynamic Categories') }}
                </span>
            </div>
            <flux:subheading>{{ __('Manage dynamic expense category scopes, colors, icons, and descriptions for your store') }}</flux:subheading>
        </div>

        @if (auth()->user()?->isSystemAdmin())
            <flux:button variant="primary" icon="plus" wire:click="openModal(null)">
                {{ __('Add Expense Category') }}
            </flux:button>
        @endif
    </div>

    <!-- Filters -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search categories...')" />

        <flux:select wire:model.live="filterType" :placeholder="__('All Scopes / Types')">
            <flux:select.option value="">{{ __('All Scopes / Types') }}</flux:select.option>
            <flux:select.option value="business">💼 {{ __('Business Operation') }}</flux:select.option>
            <flux:select.option value="consumable">🛒 {{ __('Daily Consumables') }}</flux:select.option>
            <flux:select.option value="household">🏠 {{ __('Household Expenses') }}</flux:select.option>
            <flux:select.option value="personal">👤 {{ __('Personal Admin Expenses') }}</flux:select.option>
        </flux:select>
    </div>

    <!-- Table -->
    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->categories">
            <flux:table.columns>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Scope / Type') }}</flux:table.column>
                <flux:table.column>{{ __('Description') }}</flux:table.column>
                <flux:table.column>{{ __('Total Entries') }}</flux:table.column>
                <flux:table.column>{{ __('Total Amount') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->categories as $category)
                <flux:table.row wire:key="category-{{ $category->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">{{ $category->icon ?? '📁' }}</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100 text-base">{{ $category->name }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-slate-900 dark:bg-slate-950 text-white border border-slate-700">
                            {{ ucfirst($category->type ?? 'business') }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-500 max-w-xs truncate">
                        {{ $category->description ?: '-' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge :color="$category->expenses_count > 0 ? 'emerald' : 'zinc'" size="sm">
                            {{ $category->expenses_count }} {{ __($category->expenses_count === 1 ? 'entry' : 'entries') }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="font-extrabold text-emerald-600 dark:text-emerald-400">
                        ₹{{ number_format((float) ($category->expenses_sum_amount ?? 0), 2) }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            @if (auth()->user()?->isSystemAdmin())
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openModal({{ $category->id }})" title="{{ __('Edit') }}" />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteCategory({{ $category->id }})" wire:confirm="{{ __('Delete this expense category?') }}" title="{{ __('Delete') }}" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                        {{ __('No expense categories found.') }}
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Category Modal -->
    @if (auth()->user()?->isSystemAdmin())
        <flux:modal wire:model.self="showCategoryModal" class="md:w-[520px]">
            <div class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">{{ $editingCategoryId ? __('Edit Expense Category') : __('Create Expense Category') }}</flux:heading>
                    <flux:subheading>{{ __('Configure dynamic category names, icons, scope type, and descriptions.') }}</flux:subheading>
                </div>

                <form wire:submit="saveCategory" class="flex flex-col gap-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="name" :label="__('Category Name')" placeholder="{{ __('e.g. Daily Consumables, Household Expenses') }}" required autofocus />

                        <flux:select wire:model="type" :label="__('Scope / Type')">
                            <flux:select.option value="business">💼 {{ __('Business Operation') }}</flux:select.option>
                            <flux:select.option value="consumable">🛒 {{ __('Daily Consumables') }}</flux:select.option>
                            <flux:select.option value="household">🏠 {{ __('Household Expenses') }}</flux:select.option>
                            <flux:select.option value="personal">👤 {{ __('Personal Admin Expenses') }}</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model="icon" :label="__('Icon Emoji')">
                            <flux:select.option value="🛒">🛒 Shopping / Consumables</flux:select.option>
                            <flux:select.option value="🏠">🏠 Household / Personal</flux:select.option>
                            <flux:select.option value="🚚">🚚 Freight / Transport</flux:select.option>
                            <flux:select.option value="🛠️">🛠️ Tools / Maintenance</flux:select.option>
                            <flux:select.option value="⚡">⚡ Utilities / Power</flux:select.option>
                            <flux:select.option value="👷">👷 Labour / Wages</flux:select.option>
                            <flux:select.option value="📦">📦 Raw Material</flux:select.option>
                            <flux:select.option value="💰">💰 Financial / Loan</flux:select.option>
                            <flux:select.option value="📁">📁 General Category</flux:select.option>
                        </flux:select>

                        <flux:select wire:model="color" :label="__('Badge Color')">
                            <flux:select.option value="emerald">🟢 Emerald Green</flux:select.option>
                            <flux:select.option value="amber">🟠 Amber Gold</flux:select.option>
                            <flux:select.option value="rose">🔴 Rose Pink</flux:select.option>
                            <flux:select.option value="sky">🔵 Sky Blue</flux:select.option>
                            <flux:select.option value="indigo">🟣 Indigo Violet</flux:select.option>
                            <flux:select.option value="purple">🔮 Royal Purple</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:textarea wire:model="description" :label="__('Description (Optional)')" placeholder="{{ __('Short description of what expenses belong in this category') }}" rows="2" />

                    <div class="flex justify-end gap-2 mt-2">
                        <flux:button type="button" variant="ghost" wire:click="$set('showCategoryModal', false)">{{ __('Cancel') }}</flux:button>
                        <flux:button type="submit" variant="primary">{{ __('Save Category') }}</flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    @endif
</div>

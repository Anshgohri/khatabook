<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Expenses')] class extends Component {
    use WithFileUploads, WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryId = '';

    #[Url]
    public string $filterUserId = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public bool $showForm = false;

    public bool $showCategoryModal = false;

    public ?int $editingId = null;

    public string $date = '';

    public string $expense_category_id = '';

    public string $description = '';

    public float $amount = 0;

    public string $payment_method = 'cash';

    public string $notes = '';

    public $bill_image = null;

    // Dynamic Category Properties
    public ?int $editingCategoryId = null;
    public string $newCategoryName = '';
    public string $newCategoryIcon = '🛒';
    public string $newCategoryColor = 'emerald';
    public string $newCategoryType = 'business'; // business, consumable, household, personal
    public string $newCategoryDescription = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Expense::class);
        $this->date = now()->toDateString();
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'categoryId', 'filterUserId', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function categories()
    {
        return ExpenseCategory::query()->orderBy('name')->get();
    }

    #[Computed]
    public function allUsers()
    {
        return User::query()->orderBy('name')->get();
    }

    #[Computed]
    public function expenses()
    {
        $user = Auth::user();

        return Expense::query()
            ->with(['user', 'category'])
            // If NOT System Admin, restrict strictly to logged in user's expenses
            ->when(! $user->isSystemAdmin(), fn($query) => $query->where('user_id', $user->id))
            // If System Admin selects a specific user/admin filter
            ->when($user->isSystemAdmin() && $this->filterUserId, fn($query) => $query->where('user_id', $this->filterUserId))
            ->when($this->search, fn($query) => $query->where('description', 'like', "%{$this->search}%"))
            ->when($this->categoryId, fn($query) => $query->where('expense_category_id', $this->categoryId))
            ->when($this->dateFrom, fn($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->latest('date')
            ->paginate(15);
    }

    public function createExpense(): void
    {
        $this->authorize('create', Expense::class);

        $this->reset(['editingId', 'description', 'amount', 'notes']);
        $this->date = now()->toDateString();
        $this->expense_category_id = '';
        $this->payment_method = 'cash';
        $this->bill_image = null;
        $this->showForm = true;
    }

    public function editExpense(int $expenseId): void
    {
        $expense = Expense::findOrFail($expenseId);

        $this->authorize('update', $expense);

        $this->editingId = $expense->id;
        $this->date = $expense->date->toDateString();
        $this->expense_category_id = (string) $expense->expense_category_id;
        $this->description = (string) $expense->description;
        $this->amount = (float) $expense->amount;
        $this->payment_method = $expense->payment_method;
        $this->notes = (string) $expense->notes;
        $this->bill_image = null;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'date' => ['required', 'date'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,bank_transfer,upi,cheque,other'],
            'notes' => ['nullable', 'string'],
            'bill_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $billPath = null;
        if ($this->bill_image) {
            $billPath = $this->bill_image->store('bills', 'public');
        }

        if ($this->editingId) {
            $expense = Expense::findOrFail($this->editingId);
            $this->authorize('update', $expense);

            if ($billPath) {
                $validated['bill_path'] = $billPath;
            }

            unset($validated['bill_image']);
            $expense->update($validated);
        } else {
            $this->authorize('create', Expense::class);
            unset($validated['bill_image']);

            Expense::create([
                ...$validated,
                'user_id' => Auth::id(),
                'bill_path' => $billPath,
            ]);
        }

        $this->showForm = false;
        $this->bill_image = null;
        unset($this->expenses);
        Flux::toast(variant: 'success', text: __('Expense saved.'));
    }

    public function deleteExpense(int $expenseId): void
    {
        $expense = Expense::findOrFail($expenseId);

        $this->authorize('delete', $expense);

        $expense->delete();
        unset($this->expenses);
        Flux::toast(variant: 'success', text: __('Expense deleted.'));
    }

    // Dynamic Category Management Methods
    public function openCategoryModal(?int $id = null): void
    {
        if (! Auth::user()?->isSystemAdmin()) {
            abort(403);
        }

        if ($id) {
            $cat = ExpenseCategory::findOrFail($id);
            $this->editingCategoryId = $cat->id;
            $this->newCategoryName = $cat->name;
            $this->newCategoryIcon = $cat->icon ?? '🛒';
            $this->newCategoryColor = $cat->color ?? 'emerald';
            $this->newCategoryType = $cat->type ?? 'business';
            $this->newCategoryDescription = $cat->description ?? '';
        } else {
            $this->reset(['editingCategoryId', 'newCategoryName', 'newCategoryDescription']);
            $this->newCategoryIcon = '🛒';
            $this->newCategoryColor = 'emerald';
            $this->newCategoryType = 'business';
        }

        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        if (! Auth::user()?->isSystemAdmin()) {
            abort(403);
        }

        $this->validate([
            'newCategoryName' => ['required', 'string', 'max:255', 'unique:expense_categories,name,' . ($this->editingCategoryId ?? 'NULL') . ',id'],
            'newCategoryIcon' => ['required', 'string'],
            'newCategoryColor' => ['required', 'string'],
            'newCategoryType' => ['required', 'string'],
            'newCategoryDescription' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editingCategoryId) {
            $cat = ExpenseCategory::findOrFail($this->editingCategoryId);
            $cat->update([
                'name' => $this->newCategoryName,
                'icon' => $this->newCategoryIcon,
                'color' => $this->newCategoryColor,
                'type' => $this->newCategoryType,
                'description' => $this->newCategoryDescription,
            ]);
            Flux::toast(variant: 'success', text: __('Expense category updated.'));
        } else {
            $cat = ExpenseCategory::create([
                'name' => $this->newCategoryName,
                'icon' => $this->newCategoryIcon,
                'color' => $this->newCategoryColor,
                'type' => $this->newCategoryType,
                'description' => $this->newCategoryDescription,
            ]);
            $this->expense_category_id = (string) $cat->id;
            Flux::toast(variant: 'success', text: __('Dynamic category created successfully!'));
        }

        $this->reset(['editingCategoryId', 'newCategoryName', 'newCategoryDescription']);
        $this->newCategoryIcon = '🛒';
        $this->newCategoryColor = 'emerald';
        $this->newCategoryType = 'business';
        unset($this->categories);
    }

    public function deleteCategory(int $categoryId): void
    {
        if (! Auth::user()?->isSystemAdmin()) {
            abort(403);
        }

        $category = ExpenseCategory::findOrFail($categoryId);
        if ($category->expenses()->exists()) {
            Flux::toast(variant: 'danger', text: __('Cannot delete category that has existing expenses.'));
            return;
        }

        $category->delete();
        unset($this->categories);
        Flux::toast(variant: 'success', text: __('Expense category deleted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">{{ __('Expenses') }}</flux:heading>
                @if (auth()->user()?->isSystemAdmin())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/40">
                        🛡️ {{ __('System Admin Master View') }}
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-slate-500/20 text-slate-600 dark:text-slate-300 border border-slate-500/40">
                        👤 {{ __('My Personal Expenses') }}
                    </span>
                @endif
            </div>
            <flux:subheading>
                @if (auth()->user()?->isSystemAdmin())
                    {{ __('Manage and monitor dynamic categories, business & admin expenses across the store.') }}
                @else
                    {{ __('Track your daily consumables, household, and business expenses.') }}
                @endif
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            @if (auth()->user()?->isSystemAdmin())
                <flux:button variant="subtle" icon="folder-plus" wire:click="openCategoryModal">
                    {{ __('Dynamic Categories') }}
                </flux:button>
            @endif

            @can('create', Expense::class)
                <flux:button variant="primary" icon="plus" wire:click="createExpense">
                    {{ __('Add Expense') }}
                </flux:button>
            @endcan
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="grid gap-4 sm:grid-cols-2 {{ auth()->user()?->isSystemAdmin() ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} items-end">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search description...')" icon="magnifying-glass" />

        <flux:select wire:model.live="categoryId" :placeholder="__('All Categories')">
            <flux:select.option value="">{{ __('All Categories') }}</flux:select.option>
            @foreach ($this->categories as $category)
                <flux:select.option value="{{ $category->id }}">
                    {{ $category->icon ?? '📁' }} {{ $category->name }} ({{ ucfirst($category->type ?? 'business') }})
                </flux:select.option>
            @endforeach
        </flux:select>

        @if (auth()->user()?->isSystemAdmin())
            <flux:select wire:model.live="filterUserId" :placeholder="__('All Admins / Users')">
                <flux:select.option value="">{{ __('All Admins / Users') }}</flux:select.option>
                @foreach ($this->allUsers as $u)
                    <flux:select.option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role?->name ?? 'User' }})</flux:select.option>
                @endforeach
            </flux:select>
        @endif

        <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
        <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
    </div>

    <!-- Expenses Table -->
    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->expenses">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Description') }}</flux:table.column>
                <flux:table.column>{{ __('Amount') }}</flux:table.column>
                <flux:table.column>{{ __('Payment Method') }}</flux:table.column>
                <flux:table.column>{{ __('Bill / Receipt') }}</flux:table.column>
                <flux:table.column>{{ __('Recorded By') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->expenses as $expense)
                <flux:table.row wire:key="expense-{{ $expense->id }}">
                    <flux:table.cell class="whitespace-nowrap font-medium">{{ $expense->date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @php
                            $cColor = $expense->category->color ?? 'emerald';
                            $cIcon = $expense->category->icon ?? '📁';
                            $cType = $expense->category->type ?? 'business';
                        @endphp
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-900 dark:bg-slate-950 border border-slate-700 shadow-sm text-white">
                                <span>{{ $cIcon }}</span>
                                <span>{{ $expense->category->name }}</span>
                            </span>
                            <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.2 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $cType }}
                            </span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="max-w-xs truncate">{{ $expense->description ?: '-' }}</flux:table.cell>
                    <flux:table.cell class="font-extrabold text-emerald-600 dark:text-emerald-400">₹{{ number_format((float) $expense->amount, 2) }}</flux:table.cell>
                    <flux:table.cell class="capitalize">{{ str_replace('_', ' ', $expense->payment_method) }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($expense->bill_path)
                            <a href="{{ Storage::url($expense->bill_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                📄 {{ __('View Receipt') }}
                            </a>
                        @else
                            <span class="text-zinc-400 text-xs">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $expense->user->name }}</span>
                            @if ($expense->user_id === auth()->id())
                                <span class="text-[10px] font-bold text-amber-500 bg-amber-500/10 px-1.5 py-0.2 rounded border border-amber-500/20">(You)</span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            @can('update', $expense)
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editExpense({{ $expense->id }})" title="{{ __('Edit') }}" />
                            @endcan
                            @can('delete', $expense)
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteExpense({{ $expense->id }})" wire:confirm="{{ __('Delete this expense entry?') }}" title="{{ __('Delete') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-zinc-500 py-8">
                        {{ __('No expenses found.') }}
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Add/Edit Expense Modal -->
    <flux:modal wire:model.self="showForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingId ? __('Edit Expense') : __('Add Expense') }}</flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input type="date" wire:model="date" :label="__('Date')" required />

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <flux:label>{{ __('Category') }}</flux:label>
                        @if (auth()->user()?->isSystemAdmin())
                            <button type="button" wire:click="openCategoryModal" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <span>+ New Category</span>
                            </button>
                        @endif
                    </div>
                    <flux:select wire:model="expense_category_id" :placeholder="__('Select a category')" required>
                        @foreach ($this->categories as $category)
                            <flux:select.option value="{{ $category->id }}">
                                {{ $category->icon ?? '📁' }} {{ $category->name }} ({{ ucfirst($category->type ?? 'business') }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:textarea wire:model="description" :label="__('Description / Items')" placeholder="{{ __('e.g., Tea/Snacks for office, Household groceries, Raw bamboo freight') }}" rows="2" />

                <flux:input type="number" step="0.01" min="0" wire:model="amount" :label="__('Amount (₹)')" required />

                <flux:select wire:model="payment_method" :label="__('Payment Method')">
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                    <flux:select.option value="upi">{{ __('UPI') }}</flux:select.option>
                    <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="notes" :label="__('Notes (Optional)')" rows="2" />

                <flux:input type="file" wire:model="bill_image" :label="__('Bill / Receipt Image (Optional)')" accept="image/*,.pdf" />

                <div class="flex justify-end gap-2 mt-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Expense') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- System Admin Dynamic Category Management Modal -->
    @if (auth()->user()?->isSystemAdmin())
        <flux:modal wire:model.self="showCategoryModal" class="md:w-[540px]">
            <div class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">{{ $editingCategoryId ? __('Edit Dynamic Category') : __('Create Dynamic Expense Category') }}</flux:heading>
                    <flux:subheading>{{ __('Configure dynamic category names, type, icon, and description for all store admins.') }}</flux:subheading>
                </div>

                <!-- Add/Edit Dynamic Category Form -->
                <form wire:submit="saveCategory" class="flex flex-col gap-4 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="newCategoryName" :label="__('Category Name')" placeholder="{{ __('e.g., Daily Consumables, Household') }}" required />

                        <flux:select wire:model="newCategoryType" :label="__('Scope / Type')">
                            <flux:select.option value="business">💼 {{ __('Business Operation') }}</flux:select.option>
                            <flux:select.option value="consumable">🛒 {{ __('Daily Consumable') }}</flux:select.option>
                            <flux:select.option value="household">🏠 {{ __('Household Expense') }}</flux:select.option>
                            <flux:select.option value="personal">👤 {{ __('Personal Admin Expense') }}</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model="newCategoryIcon" :label="__('Icon Emoji')">
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

                        <flux:select wire:model="newCategoryColor" :label="__('Badge Color')">
                            <flux:select.option value="emerald">🟢 Emerald Green</flux:select.option>
                            <flux:select.option value="amber">🟠 Amber Gold</flux:select.option>
                            <flux:select.option value="rose">🔴 Rose Pink</flux:select.option>
                            <flux:select.option value="sky">🔵 Sky Blue</flux:select.option>
                            <flux:select.option value="indigo">🟣 Indigo Violet</flux:select.option>
                            <flux:select.option value="purple">🔮 Royal Purple</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:input wire:model="newCategoryDescription" :label="__('Description (Optional)')" placeholder="{{ __('Short description of what goes into this category') }}" />

                    <div class="flex justify-end gap-2 mt-1">
                        @if ($editingCategoryId)
                            <flux:button type="button" variant="ghost" wire:click="openCategoryModal(null)">{{ __('Cancel Edit') }}</flux:button>
                        @endif
                        <flux:button type="submit" variant="primary" icon="check">
                            {{ $editingCategoryId ? __('Update Category') : __('Create Dynamic Category') }}
                        </flux:button>
                    </div>
                </form>

                <!-- Existing Categories List -->
                <div class="flex flex-col gap-2.5 border-t border-zinc-200 dark:border-zinc-700 pt-4 max-h-64 overflow-y-auto">
                    <flux:heading size="sm">{{ __('Configured Dynamic Categories') }}</flux:heading>
                    @foreach ($this->categories as $category)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="text-xl">{{ $category->icon ?? '📁' }}</span>
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100 truncate">{{ $category->name }}</span>
                                        <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            {{ $category->type ?? 'business' }}
                                        </span>
                                    </div>
                                    @if ($category->description)
                                        <span class="text-xs text-zinc-500 truncate">{{ $category->description }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openCategoryModal({{ $category->id }})" title="{{ __('Edit Category') }}" />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteCategory({{ $category->id }})" wire:confirm="{{ __('Delete this expense category?') }}" title="{{ __('Delete Category') }}" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <flux:button type="button" variant="ghost" wire:click="$set('showCategoryModal', false)">{{ __('Close') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
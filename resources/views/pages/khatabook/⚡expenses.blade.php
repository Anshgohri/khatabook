<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Expenses')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryId = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $date = '';

    public string $expense_category_id = '';

    public string $description = '';

    public float $amount = 0;

    public string $payment_method = 'cash';

    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Expense::class);
        $this->date = now()->toDateString();
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'categoryId', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function categories()
    {
        return ExpenseCategory::query()->orderBy('name')->get();
    }

    #[Computed]
    public function expenses()
    {
        $user = Auth::user();

        return Expense::query()
            ->with(['user', 'category'])
            ->when($user->isStaff(), fn($query) => $query->where('user_id', $user->id))
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
        ]);

        if ($this->editingId) {
            $expense = Expense::findOrFail($this->editingId);
            $this->authorize('update', $expense);
            $expense->update($validated);
        } else {
            $this->authorize('create', Expense::class);
            Expense::create([...$validated, 'user_id' => Auth::id()]);
        }

        $this->showForm = false;
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
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:heading size="xl">{{ __('Expenses') }}</flux:heading>

        @can('create', Expense::class)
        <flux:button variant="primary" icon="plus" wire:click="createExpense">{{ __('Add expense') }}</flux:button>
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search description...')" icon="magnifying-glass" />

        <flux:select wire:model.live="categoryId" :placeholder="__('All categories')">
            <flux:select.option value="">{{ __('All categories') }}</flux:select.option>
            @foreach ($this->categories as $category)
            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
        <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->expenses">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Description') }}</flux:table.column>
                <flux:table.column>{{ __('Amount') }}</flux:table.column>
                <flux:table.column>{{ __('Payment method') }}</flux:table.column>
                <flux:table.column>{{ __('Recorded by') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->expenses as $expense)
                <flux:table.row wire:key="expense-{{ $expense->id }}">
                    <flux:table.cell>{{ $expense->date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>{{ $expense->category->name }}</flux:table.cell>
                    <flux:table.cell>{{ $expense->description }}</flux:table.cell>
                    <flux:table.cell>{{ number_format((float) $expense->amount, 2) }}</flux:table.cell>
                    <flux:table.cell>{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</flux:table.cell>
                    <flux:table.cell>{{ $expense->user->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $expense)
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editExpense({{ $expense->id }})" />
                            @endcan
                            @can('delete', $expense)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteExpense({{ $expense->id }})" wire:confirm="{{ __('Delete this expense?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center text-zinc-500">{{ __('No expenses found.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal wire:model.self="showForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingId ? __('Edit expense') : __('Add expense') }}</flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input type="date" wire:model="date" :label="__('Date')" required />

                <flux:select wire:model="expense_category_id" :label="__('Category')" :placeholder="__('Select a category')">
                    @foreach ($this->categories as $category)
                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="description" :label="__('Description')" rows="2" />
                <flux:input type="number" step="0.01" min="0" wire:model="amount" :label="__('Amount')" required />

                <flux:select wire:model="payment_method" :label="__('Payment method')">
                    <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                    <flux:select.option value="bank_transfer">{{ __('Bank transfer') }}</flux:select.option>
                    <flux:select.option value="upi">{{ __('UPI') }}</flux:select.option>
                    <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                    <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                </flux:select>

                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
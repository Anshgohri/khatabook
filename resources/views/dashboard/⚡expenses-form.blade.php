<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Expense Form')] class extends Component {
    use WithFileUploads;

    public ?Expense $expense = null;
    public ?int $editingId = null;

    public string $date = '';
    public string $expense_category_id = '';
    public string $description = '';
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $notes = '';
    public $bill_image = null;
    public ?string $existing_bill_path = null;

    public function mount(?Expense $expense = null): void
    {
        if ($expense && $expense->exists) {
            $this->authorize('update', $expense);

            $this->expense = $expense;
            $this->editingId = $expense->id;
            $this->date = $expense->date->toDateString();
            $this->expense_category_id = (string) $expense->expense_category_id;
            $this->description = (string) ($expense->description ?? '');
            $this->amount = (float) $expense->amount;
            $this->payment_method = $expense->payment_method;
            $this->notes = (string) ($expense->notes ?? '');
            $this->existing_bill_path = $expense->bill_path;
        } else {
            $this->authorize('create', Expense::class);
            $this->date = now()->toDateString();
            $this->payment_method = 'cash';
            $this->amount = null;
        }
    }

    #[Computed]
    public function categories()
    {
        return ExpenseCategory::query()->orderBy('name')->get();
    }

    public function removeExistingBill(): void
    {
        if ($this->editingId && $this->expense) {
            $this->authorize('update', $this->expense);
            if ($this->expense->bill_path && Storage::exists($this->expense->bill_path)) {
                Storage::delete($this->expense->bill_path);
            }
            $this->expense->update(['bill_path' => null]);
            $this->existing_bill_path = null;
            Flux::toast(variant: 'success', text: __('Receipt removed.'));
        }
    }

    public function save()
    {
        if ($this->bill_image && method_exists($this->bill_image, 'exists') && ! $this->bill_image->exists()) {
            $this->addError('bill_image', __('The uploaded receipt file is no longer available on the server. Please select the file again.'));
            return;
        }

        $validated = $this->validate([
            'date' => ['required', 'date'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank_transfer,upi,cheque,other'],
            'notes' => ['nullable', 'string'],
            'bill_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $billPath = null;
        if ($this->bill_image) {
            if ($this->editingId && $this->expense?->bill_path && Storage::exists($this->expense->bill_path)) {
                Storage::delete($this->expense->bill_path);
            }
            $billPath = $this->bill_image->store('bills');
        }

        if ($this->editingId) {
            $expense = Expense::findOrFail($this->editingId);
            $this->authorize('update', $expense);

            if ($billPath) {
                $validated['bill_path'] = $billPath;
            }

            unset($validated['bill_image']);
            $expense->update($validated);
            $msg = __('Expense updated successfully.');
        } else {
            $this->authorize('create', Expense::class);
            unset($validated['bill_image']);

            Expense::create([
                ...$validated,
                'user_id' => Auth::id(),
                'bill_path' => $billPath,
            ]);
            $msg = __('Expense recorded successfully.');
        }

        Flux::toast(variant: 'success', text: $msg);
        $this->redirect(route('expenses'), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('expenses')" wire:navigate />
            <div>
                <flux:heading size="xl">{{ $this->editingId ? __('Edit Expense') : __('Add New Expense') }}</flux:heading>
                <flux:subheading size="sm">
                    {{ $this->editingId ? __('Update expense record, category classification, and attached invoice.') : __('Record an operational, consumable, or business expense entry.') }}
                </flux:subheading>
            </div>
        </div>

        @if ($this->editingId && $this->expense)
            <div class="flex items-center gap-2">
                <flux:button variant="subtle" icon="eye" :href="route('expenses.show', $this->expense->id)" wire:navigate>
                    {{ __('View Details') }}
                </flux:button>
            </div>
        @endif
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <!-- Expense Details Card -->
        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ __('Expense Information') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('Amount, category, date, and payment method.') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:input type="date" wire:model="date" :label="__('Expense Date')" required />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <flux:label>{{ __('Category') }}</flux:label>
                        <a href="{{ route('expense-categories') }}" target="_blank" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                            + {{ __('Categories') }} ↗
                        </a>
                    </div>
                    <flux:select wire:model="expense_category_id" :placeholder="__('Select a category')" required>
                        <flux:select.option value="">{{ __('-- Select Category --') }}</flux:select.option>
                        @foreach ($this->categories as $category)
                            <flux:select.option value="{{ $category->id }}">
                                {{ $category->icon ?? '📁' }} {{ $category->name }} ({{ ucfirst($category->type ?? 'business') }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:input type="number" step="0.01" min="0.01" wire:model="amount" :label="__('Amount (₹)')" placeholder="e.g. 2500" required />
                </div>

                <div>
                    <flux:select wire:model="payment_method" :label="__('Payment Method')" required>
                        <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                        <flux:select.option value="upi">{{ __('UPI / Google Pay / PhonePe') }}</flux:select.option>
                        <flux:select.option value="bank_transfer">{{ __('Bank Transfer / NEFT / IMPS') }}</flux:select.option>
                        <flux:select.option value="cheque">{{ __('Cheque') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="sm:col-span-2">
                    <flux:textarea wire:model="description" :label="__('Description / Item Details')" placeholder="{{ __('e.g., Tea and snacks for office staff, Bamboo freight transport, Warehouse electricity bill...') }}" rows="2" />
                </div>
            </div>
        </flux:card>

        <!-- Receipt & Notes Card -->
        <flux:card class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg">{{ __('Documentation & Notes') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('Attach receipt photos, bills, or additional notes.') }}</flux:text>
            </div>

            <div>
                <flux:textarea wire:model="notes" :label="__('Internal Notes / Remarks (Optional)')" rows="2" placeholder="{{ __('Optional remarks or context for accounting...') }}" />
            </div>

            <div class="flex flex-col gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:label>{{ __('Bill / Receipt Document') }}</flux:label>

                @if ($this->existing_bill_path)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 text-xs border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-2 truncate">
                            <span>📄</span>
                            <a href="{{ Storage::url($this->existing_bill_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 underline font-semibold truncate">
                                {{ basename($this->existing_bill_path) }}
                            </a>
                            <span class="text-zinc-400">({{ __('Current attachment') }})</span>
                        </div>
                        <button type="button" wire:click="removeExistingBill" class="text-rose-600 hover:text-rose-700 dark:text-rose-400 font-semibold shrink-0 ml-3">
                            {{ __('Remove Receipt') }}
                        </button>
                    </div>
                @endif

                <flux:input type="file" wire:model="bill_image" :label="$this->existing_bill_path ? __('Replace Receipt Image / PDF') : null" accept="image/*,.pdf" />
                <div class="flex items-center justify-between text-xs text-zinc-500">
                    <span>{{ __('Supported formats: JPG, PNG, WEBP, PDF (Max 10MB)') }}</span>
                    <span wire:loading wire:target="bill_image" class="text-indigo-600 dark:text-indigo-400 font-medium">{{ __('Uploading receipt...') }}</span>
                </div>
            </div>
        </flux:card>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" :href="route('expenses')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $this->editingId ? __('Update Expense') : __('Save Expense') }}</span>
                <span wire:loading>{{ __('Saving...') }}</span>
            </flux:button>
        </div>
    </form>
</div>

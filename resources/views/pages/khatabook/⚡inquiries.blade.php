<?php

use App\Models\ContactInquiry;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Contact Inquiries')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Access denied. Only administrators can view customer contact inquiries.');
        }
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function updateStatus(int $id, string $newStatus): void
    {
        if (! auth()->user()?->isAdmin()) {
            return;
        }

        $inquiry = ContactInquiry::findOrFail($id);
        $inquiry->update(['status' => $newStatus]);
    }

    public function deleteInquiry(int $id): void
    {
        if (! auth()->user()?->isAdmin()) {
            return;
        }

        $inquiry = ContactInquiry::findOrFail($id);
        $inquiry->delete();
    }

    #[Computed]
    public function inquiries()
    {
        return ContactInquiry::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('inquiry_type', 'like', "%{$this->search}%");
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest('created_at')
            ->paginate(15);
    }

    #[Computed]
    public function counts()
    {
        return [
            'total' => ContactInquiry::count(),
            'pending' => ContactInquiry::where('status', 'pending')->count(),
            'contacted' => ContactInquiry::where('status', 'contacted')->count(),
            'resolved' => ContactInquiry::where('status', 'resolved')->count(),
        ];
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Customer Contact Inquiries') }}</flux:heading>
            <flux:subheading>{{ __('Manage incoming bamboo and scaffolding customer inquiries submitted on the website.') }}</flux:subheading>
        </div>
    </div>

    <!-- Summary Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm">
            <span class="text-xs text-zinc-500 dark:text-zinc-400 font-semibold block">{{ __('Total Inquiries') }}</span>
            <span class="text-2xl font-extrabold text-zinc-900 dark:text-white mt-1 block">{{ $this->counts['total'] }}</span>
        </div>
        <div class="p-4 rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50/50 dark:bg-amber-950/30 shadow-sm">
            <span class="text-xs text-amber-700 dark:text-amber-300 font-semibold block">{{ __('Pending Response') }}</span>
            <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 block">{{ $this->counts['pending'] }}</span>
        </div>
        <div class="p-4 rounded-xl border border-sky-200 dark:border-sky-900/60 bg-sky-50/50 dark:bg-sky-950/30 shadow-sm">
            <span class="text-xs text-sky-700 dark:text-sky-300 font-semibold block">{{ __('Contacted') }}</span>
            <span class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1 block">{{ $this->counts['contacted'] }}</span>
        </div>
        <div class="p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/50 dark:bg-emerald-950/30 shadow-sm">
            <span class="text-xs text-emerald-700 dark:text-emerald-300 font-semibold block">{{ __('Resolved') }}</span>
            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $this->counts['resolved'] }}</span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="grid gap-4 sm:grid-cols-2">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search name, phone, or requirement...')" />

        <flux:select wire:model.live="status" :placeholder="__('Filter by status')">
            <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
            <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
            <flux:select.option value="contacted">{{ __('Contacted') }}</flux:select.option>
            <flux:select.option value="resolved">{{ __('Resolved') }}</flux:select.option>
        </flux:select>
    </div>

    <!-- Inquiries Table -->
    <flux:table :paginate="$this->inquiries">
        <flux:table.columns>
            <flux:table.column>{{ __('Date') }}</flux:table.column>
            <flux:table.column>{{ __('Customer Details') }}</flux:table.column>
            <flux:table.column>{{ __('Inquiry Type') }}</flux:table.column>
            <flux:table.column>{{ __('Message / Details') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column class="text-end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->inquiries as $inquiry)
                <flux:table.row wire:key="inquiry-{{ $inquiry->id }}">
                    <flux:table.cell class="whitespace-nowrap font-medium text-xs">
                        {{ $inquiry->created_at->format('d M Y') }}<br>
                        <span class="text-zinc-500 dark:text-zinc-400">{{ $inquiry->created_at->format('H:i A') }}</span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-bold text-zinc-900 dark:text-white">{{ $inquiry->name }}</div>
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">📞 {{ $inquiry->phone }}</div>
                        @if ($inquiry->email)
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">✉️ {{ $inquiry->email }}</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge color="zinc" size="sm" class="font-semibold">
                            {{ $inquiry->inquiry_type }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="max-w-xs text-xs text-zinc-700 dark:text-zinc-300">
                        <div class="line-clamp-3">{{ $inquiry->message }}</div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge :color="match ($inquiry->status) { 'pending' => 'amber', 'contacted' => 'sky', 'resolved' => 'green', default => 'zinc' }" size="sm">
                            {{ ucfirst($inquiry->status) }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="text-end">
                        <div class="flex items-center justify-end gap-1">
                            @if ($inquiry->status === 'pending')
                                <flux:button wire:click="updateStatus({{ $inquiry->id }}, 'contacted')" size="xs" variant="filled">
                                    {{ __('Mark Contacted') }}
                                </flux:button>
                            @endif

                            @if ($inquiry->status !== 'resolved')
                                <flux:button wire:click="updateStatus({{ $inquiry->id }}, 'resolved')" size="xs" variant="filled" color="green">
                                    {{ __('Resolve') }}
                                </flux:button>
                            @endif

                            <flux:button wire:click="deleteInquiry({{ $inquiry->id }})" wire:confirm="Are you sure you want to delete this inquiry?" size="xs" variant="ghost" color="red">
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                        {{ __('No customer inquiries found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>

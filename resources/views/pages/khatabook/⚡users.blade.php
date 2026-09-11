<?php

use App\Actions\InviteUser;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Users')] class extends Component {
    use WithPagination;

    public bool $showInviteForm = false;

    public string $invite_name = '';

    public string $invite_email = '';

    public string $invite_phone = '';

    public string $invite_address = '';

    public string $invite_city = '';

    public string $invite_role_id = '';

    public bool $showEditForm = false;

    public ?int $editingId = null;

    public string $edit_name = '';

    public string $edit_phone = '';

    public string $edit_address = '';

    public string $edit_city = '';

    public string $edit_role_id = '';

    public string $edit_status = 'active';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('name')->get();
    }

    #[Computed]
    public function users()
    {
        return User::query()->with('role')->orderBy('name')->paginate(15);
    }

    public function inviteUser(): void
    {
        $this->authorize('create', User::class);

        $validated = $this->validate([
            'invite_name' => ['required', 'string', 'max:255'],
            'invite_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'invite_phone' => ['nullable', 'regex:/^[0-9]+$/', 'max:50', Rule::unique('users', 'phone')],
            'invite_address' => ['nullable', 'string', 'max:255'],
            'invite_city' => ['nullable', 'string', 'max:100'],
            'invite_role_id' => ['required', 'exists:roles,id'],
        ], [
            'invite_email.unique' => __('The email address has already been registered to another user.'),
            'invite_phone.regex' => __('The phone number must contain only numbers.'),
            'invite_phone.unique' => __('The phone number has already been registered to another user.'),
        ]);

        app(InviteUser::class)->invite(
            $validated['invite_name'],
            $validated['invite_email'],
            Role::findOrFail($validated['invite_role_id']),
            $validated['invite_phone'] ?: null,
            $validated['invite_address'] ?: null,
            $validated['invite_city'] ?: null,
        );

        $this->reset(['invite_name', 'invite_email', 'invite_phone', 'invite_address', 'invite_city', 'invite_role_id']);
        $this->showInviteForm = false;
        unset($this->users);
        Flux::toast(variant: 'success', text: __('Invitation sent.'));
    }

    public function editUser(int $userId): void
    {
        $target = User::findOrFail($userId);

        $this->authorize('update', $target);

        $this->editingId = $target->id;
        $this->edit_name = $target->name;
        $this->edit_phone = $target->phone ?? '';
        $this->edit_address = $target->address ?? '';
        $this->edit_city = $target->city ?? '';
        $this->edit_role_id = (string) $target->role_id;
        $this->edit_status = $target->status;
        $this->showEditForm = true;
    }

    public function saveUser(): void
    {
        $target = User::findOrFail($this->editingId);

        $this->authorize('update', $target);

        $validated = $this->validate([
            'edit_name' => ['required', 'string', 'max:255'],
            'edit_phone' => ['nullable', 'regex:/^[0-9]+$/', 'max:50', Rule::unique('users', 'phone')->ignore($this->editingId)],
            'edit_address' => ['nullable', 'string', 'max:255'],
            'edit_city' => ['nullable', 'string', 'max:100'],
            'edit_role_id' => ['required', 'exists:roles,id'],
            'edit_status' => ['required', 'in:active,disabled'],
        ], [
            'edit_phone.regex' => __('The phone number must contain only numbers.'),
            'edit_phone.unique' => __('The phone number has already been registered to another user.'),
        ]);

        $target->update([
            'name' => $validated['edit_name'],
            'phone' => $validated['edit_phone'] ?: null,
            'address' => $validated['edit_address'] ?: null,
            'city' => $validated['edit_city'] ?: null,
            'role_id' => $validated['edit_role_id'],
            'status' => $validated['edit_status'],
        ]);

        $this->showEditForm = false;
        unset($this->users);
        Flux::toast(variant: 'success', text: __('User updated.'));
    }

    public function deleteUser(int $userId): void
    {
        $target = User::findOrFail($userId);

        $this->authorize('delete', $target);

        $target->delete();
        unset($this->users);
        Flux::toast(variant: 'success', text: __('User deleted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:heading size="xl">{{ __('Users & Customers') }}</flux:heading>

        @can('create', App\Models\User::class)
        <flux:button variant="primary" icon="plus" wire:click="$set('showInviteForm', true)">{{ __('Invite user') }}</flux:button>
        @endcan
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Contact Details') }}</flux:table.column>
                <flux:table.column>{{ __('Address / City') }}</flux:table.column>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->users as $targetUser)
                <flux:table.row wire:key="user-{{ $targetUser->id }}">
                    <flux:table.cell class="font-medium">{{ $targetUser->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col text-xs">
                            @if (!str_contains($targetUser->email, '@khatabook.customer'))
                            <span class="text-zinc-700 dark:text-zinc-300">{{ $targetUser->email }}</span>
                            @endif
                            @if ($targetUser->phone)
                            <span class="text-zinc-500">{{ $targetUser->phone }}</span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col text-xs text-zinc-600 dark:text-zinc-400">
                            @if ($targetUser->city)
                            <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $targetUser->city }}</span>
                            @endif
                            @if ($targetUser->address)
                            <span class="truncate max-w-xs">{{ $targetUser->address }}</span>
                            @endif
                            @if (!$targetUser->city && !$targetUser->address)
                            <span class="text-zinc-400">—</span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match ($targetUser->role?->name) { 'Admin' => 'purple', 'Manager' => 'blue', 'ROLE_CUSTOMER' => 'emerald', default => 'zinc' }" size="sm">
                            {{ $targetUser->role?->name ?? __('None') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match ($targetUser->status) { 'active' => 'green', 'invited' => 'amber', default => 'red' }" size="sm">
                            {{ ucfirst($targetUser->status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $targetUser)
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="editUser({{ $targetUser->id }})" />
                            @endcan
                            @can('delete', $targetUser)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteUser({{ $targetUser->id }})" wire:confirm="{{ __('Delete this user?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal wire:model.self="showInviteForm" class="md:w-[480px]">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Invite / Create User') }}</flux:heading>

            <form wire:submit="inviteUser" class="flex flex-col gap-4">
                <flux:input wire:model="invite_name" :label="__('Full Name')" placeholder="e.g. Rahul Sharma" required autofocus />
                <flux:input type="email" wire:model="invite_email" :label="__('Email Address')" placeholder="e.g. rahul@example.com" required />
                <flux:input type="tel" wire:model="invite_phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />

                <div class="grid grid-cols-2 gap-3">
                    <flux:input wire:model="invite_city" :label="__('City')" placeholder="e.g. Jaipur" />
                    <flux:select wire:model="invite_role_id" :label="__('Role')" :placeholder="__('Select a role')" required>
                        @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input wire:model="invite_address" :label="__('Full Address')" placeholder="e.g. 123 Station Road, Jaipur" />

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showInviteForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Send invite') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model.self="showEditForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Edit user') }}</flux:heading>

            <form wire:submit="saveUser" class="flex flex-col gap-4">
                <flux:input wire:model="edit_name" :label="__('Name')" required />
                <flux:input type="tel" wire:model="edit_phone" :label="__('Phone Number')" placeholder="e.g. 9876543210" />
                <flux:input wire:model="edit_city" :label="__('City')" />
                <flux:input wire:model="edit_address" :label="__('Address')" />

                <flux:select wire:model="edit_role_id" :label="__('Role')">
                    @foreach ($this->roles as $role)
                    <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="edit_status" :label="__('Status')">
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="disabled">{{ __('Disabled') }}</flux:select.option>
                </flux:select>

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showEditForm', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
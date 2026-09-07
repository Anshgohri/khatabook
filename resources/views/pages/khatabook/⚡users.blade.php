<?php

use App\Actions\InviteUser;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Users')] class extends Component {
    use WithPagination;

    public bool $showInviteForm = false;

    public string $invite_name = '';

    public string $invite_email = '';

    public string $invite_role_id = '';

    public bool $showEditForm = false;

    public ?int $editingId = null;

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
            'invite_role_id' => ['required', 'exists:roles,id'],
        ]);

        app(InviteUser::class)->invite(
            $validated['invite_name'],
            $validated['invite_email'],
            Role::findOrFail($validated['invite_role_id']),
        );

        $this->reset(['invite_name', 'invite_email', 'invite_role_id']);
        $this->showInviteForm = false;
        unset($this->users);
        Flux::toast(variant: 'success', text: __('Invitation sent.'));
    }

    public function editUser(int $userId): void
    {
        $target = User::findOrFail($userId);

        $this->authorize('update', $target);

        $this->editingId = $target->id;
        $this->edit_role_id = (string) $target->role_id;
        $this->edit_status = $target->status;
        $this->showEditForm = true;
    }

    public function saveUser(): void
    {
        $target = User::findOrFail($this->editingId);

        $this->authorize('update', $target);

        $validated = $this->validate([
            'edit_role_id' => ['required', 'exists:roles,id'],
            'edit_status' => ['required', 'in:active,disabled'],
        ]);

        $target->update([
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
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Users') }}</flux:heading>

        @can('create', App\Models\User::class)
            <flux:button variant="primary" icon="plus" wire:click="$set('showInviteForm', true)">{{ __('Invite user') }}</flux:button>
        @endcan
    </div>

    <flux:table :paginate="$this->users">
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->users as $targetUser)
                <flux:table.row wire:key="user-{{ $targetUser->id }}">
                    <flux:table.cell>{{ $targetUser->name }}</flux:table.cell>
                    <flux:table.cell>{{ $targetUser->email }}</flux:table.cell>
                    <flux:table.cell>{{ $targetUser->role?->name ?? __('None') }}</flux:table.cell>
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

    <flux:modal wire:model.self="showInviteForm" class="md:w-96">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Invite user') }}</flux:heading>

            <form wire:submit="inviteUser" class="flex flex-col gap-4">
                <flux:input wire:model="invite_name" :label="__('Name')" required autofocus />
                <flux:input type="email" wire:model="invite_email" :label="__('Email')" required />

                <flux:select wire:model="invite_role_id" :label="__('Role')" :placeholder="__('Select a role')">
                    @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="flex justify-end gap-2">
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

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\Auditable;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property int|null $role_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $city
 * @property string $status
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $avatar_path
 * @property Role|null $role
 */
#[Fillable(['name', 'email', 'password', 'role_id', 'phone', 'address', 'city', 'status', 'avatar_path'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Return the public URL for the user's avatar, or null if none exists.
     */
    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::url($this->avatar_path);
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return HasMany<Sale, $this>
     */
    public function customerSales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }

    public function isSystemAdmin(): bool
    {
        return $this->role?->name === RoleName::SystemAdmin->value || $this->role?->name === 'System Admin';
    }

    public function isAdmin(): bool
    {
        return $this->isSystemAdmin() || $this->role?->name === RoleName::Admin->value;
    }

    public function isManager(): bool
    {
        return $this->role?->name === RoleName::Manager->value;
    }

    public function isStaff(): bool
    {
        return $this->role?->name === RoleName::Staff->value;
    }

    public function isViewer(): bool
    {
        return $this->role?->name === RoleName::Viewer->value;
    }

    public function isEmployee(): bool
    {
        return $this->role?->name === RoleName::Employee->value;
    }

    public function isFinancier(): bool
    {
        return $this->role?->name === RoleName::Financier->value;
    }

    public function isCustomer(): bool
    {
        return $this->role?->name === RoleName::Customer->value;
    }

    public function isSupplier(): bool
    {
        return $this->role?->name === RoleName::Supplier->value || $this->role?->name === 'ROLE_SUPPLIERS' || $this->role?->name === 'ROLE_SUPPLIER';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Supplier, $this>
     */
    public function supplierRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Supplier::class, 'supplier_user_id');
    }
}

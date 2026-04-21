<?php

namespace Modules\User\Models;

use Arr;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Modules\ActivityLog\Traits\HasActivityLog;
use Modules\Branch\Models\Branch;
use Modules\Branch\Traits\HasBranch;
use Modules\Printer\Models\Printer;
use Modules\Support\Traits\HasActiveStatus;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasProfilePhoto;
use Modules\Support\Traits\HasSortBy;
use Modules\Support\Traits\HasTagsCache;
use Modules\User\Database\Factories\UserFactory;
use Modules\User\Enums\CustomerType;
use Modules\User\Enums\DefaultRole;
use Modules\User\Enums\GenderType;
use Modules\User\Traits\HasRoles;
use Yadahan\AuthenticationLog\AuthenticationLogable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string|null $email
 * @property GenderType|null $gender
 * @property string|null $password
 * @property string|null $phone_country_iso_code
 * @property string|null $phone
 * @property string|null $birthdate
 * @property string|null $note
 * @property CustomerType|null $customer_type
 * @property string|null $registration_number
 * @property string|null $vat_tin
 * @property array|null $profile_photo_path
 * @property string|null $national_phone
 * @property-read Branch|null $effective_branch
 * @property array|null $category_slugs
 * @property int|null $printer_id
 * @property-read  Printer|null $printer
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    use
        AuthenticationLogable,
        HasActiveStatus,
        HasActivityLog,
        HasApiTokens,
        HasDatabaseNotifications,
        HasFactory,
        HasProfilePhoto,
        HasRoles,
        HasCreatedBy,
        HasBranch,
        HasTagsCache,
        HasSortBy,
        HasFilters,
        Notifiable,
        SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'gender',
        'phone_country_iso_code',
        'phone',
        'birthdate',
        'note',
        'customer_type',
        'registration_number',
        'vat_tin',
        'profile_photo_path',
        'category_slugs',
        'printer_id',
        self::BRANCH_COLUMN_NAME,
        self::ACTIVE_COLUMN_NAME,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ["roles"];

    /**
     * Get a list of all suppliers.
     *
     * @param int|null $branchId
     * @param DefaultRole|array<DefaultRole>|null $defaultRole
     * @param DefaultRole|array<DefaultRole>|null $withoutDefaultRole
     * @return Collection
     */
    public static function list(
        ?int                   $branchId = null,
        DefaultRole|array|null $defaultRole = null,
        DefaultRole|array|null $withoutDefaultRole = null
    ): Collection
    {
        $defaultRoles = collect(Arr::wrap($defaultRole))
            ->flatten()
            ->map(fn(DefaultRole $role) => $role->value)
            ->values();

        $withoutDefaultRoles = collect(Arr::wrap($withoutDefaultRole))
            ->flatten()
            ->map(fn(DefaultRole $role) => $role->value)
            ->values();

        return Cache::tags('users')->rememberForever(
            makeCacheKey(
                [
                    'users',
                    $branchId ? "branch-$branchId" : 'all',
                    $defaultRoles->isNotEmpty()
                        ? 'default-role-' . $defaultRoles->implode(',')
                        : 'default-role-all',
                    $withoutDefaultRoles->isNotEmpty()
                        ? 'without-default-role-' . $withoutDefaultRoles->implode(',')
                        : 'without-default-role-all',
                    'list',
                ],
                false
            ),
            fn() => static::query()
                ->without('roles')
                ->select('id', 'name', 'phone', 'phone_country_iso_code')
                ->when(
                    $branchId,
                    fn($query) => $query
                        ->where('branch_id', $branchId)
                        ->orWhereNull('branch_id')
                )
                ->when(
                    $defaultRoles->isNotEmpty(),
                    fn($query) => $query->whereHas(
                        'roles',
                        fn($q) => $q->whereIn('name', $defaultRoles)
                    )
                )
                ->when(
                    $withoutDefaultRoles->isNotEmpty(),
                    fn($query) => $query->whereDoesntHave(
                        'roles',
                        fn($q) => $q->whereIn('name', $withoutDefaultRoles)
                    )
                )
                ->get()
                ->map(fn(User $user) => [
                    'id' => $user->id,
                    'name' => $user->name
                        . (is_null($user->national_phone)
                            ? ''
                            : ' (' . str_replace(' ', '', $user->national_phone) . ')'),
                ])
        );
    }

    /**
     * Get walk-in Name
     * @return string
     */
    public static function walkInName(): string
    {
        return "Walk-In Customer";
    }

    /**
     * Create a new instance of the factory for generating AcademicYear models.
     *
     * @return UserFactory
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /** @inheritDoc */
    public function allowedFilterKeys(): array
    {
        return [
            "search",
            "from",
            "to",
            "role",
            "gender",
            self::ACTIVE_COLUMN_NAME,
            self::BRANCH_COLUMN_NAME
        ];
    }

    /**
     * Determine if the main user
     * @return bool
     */
    public function isMainUser(): bool
    {
        return $this->id === 1;
    }

    /**
     * Scope a query to search across all fields.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeSearch(Builder $query, string $value): void
    {
        $query->where(function ($query) use ($value) {
            $query->like('name', $value)
                ->orLike('username', $value)
                ->orLike('phone', $value)
                ->orLike('email', $value);
        });

    }

    /**
     * Get effective branch
     *
     * @return Attribute
     */
    public function effectiveBranch(): Attribute
    {
        return Attribute::get(fn() => $this->assignedToBranch() ? $this->branch : Branch::main()->first());
    }

    /**
     * Determine if user assigned to branch or not
     *
     * @return bool
     */
    public function assignedToBranch(): bool
    {
        return !is_null($this->branch_id);
    }

    /**
     * Phone attribute
     *
     * @return Attribute
     */
    public function phone(): Attribute
    {
        return Attribute::make(
            get: fn($phone) => !is_null($phone)
                ? phone($phone, $this->phone_country_iso_code)->formatInternational()
                : null,
        );
    }

    /**
     * National mobile number attribute
     *
     * @return Attribute
     */
    public function nationalPhone(): Attribute
    {
        return Attribute::get(
            fn() => !is_null($this->phone)
                ? phone($this->phone, $this->phone_country_iso_code)->formatNational()
                : null
        );
    }

    /**
     * Get printer
     *
     * @return BelongsTo
     */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    /** @inheritDoc */
    protected function getSortableAttributes(): array
    {
        return [
            "name",
            "email",
            "username",
            "gender",
            "phone"
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'gender' => GenderType::class,
            'customer_type' => CustomerType::class,
            'is_active' => 'boolean',
            'birthdate' => 'date:Y-m-d',
            'profile_photo_path' => 'array',
            'category_slugs' => 'array',
        ];
    }
}

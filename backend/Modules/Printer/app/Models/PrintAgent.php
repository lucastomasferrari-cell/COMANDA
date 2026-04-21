<?php

namespace Modules\Printer\Models;

use Carbon\Carbon;
use Modules\ActivityLog\Traits\HasActivityLog;
use Modules\Branch\Traits\HasBranch;
use Modules\Support\Eloquent\Model;
use Modules\Support\Traits\HasActiveStatus;
use Modules\Support\Traits\HasCreatedBy;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;
use Modules\Translation\Traits\Translatable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $api_key
 * @property string|null $host
 * @property int|null $port
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PrintAgent extends Model
{
    use HasCreatedBy,
        HasActiveStatus,
        HasBranch,
        HasSortBy,
        HasFilters,
        Translatable,
        HasActivityLog;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        self::BRANCH_COLUMN_NAME,
        self::ACTIVE_COLUMN_NAME,
        'name',
        'api_key',
        'host',
        'port',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatable = ['name'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(fn(PrintAgent $agent) => $agent->syncActiveAgent());
    }

    /**
     * Sync active agent
     *
     * @return void
     */
    public function syncActiveAgent(): void
    {
        $this->withoutEvents(function () {
            $branchId = $this->branch_id;
            if ($this->is_active) {
                self::withoutGlobalActive()
                    ->withOutGlobalBranchPermission()
                    ->where('id', '<>', $this->id)
                    ->where('branch_id', $branchId)
                    ->update(['is_active' => false]);
            } elseif (!static::hasActiveAgent($this->id, $branchId)) {
                self::withoutGlobalActive()
                    ->withOutGlobalBranchPermission()
                    ->where('id', $this->id)
                    ->where('branch_id', $branchId)
                    ->update(['is_active' => true]);
            }
        });
    }

    /**
     * Determine id has active agent
     *
     * @param int|null $exceptionId
     * @param int|null $branchId
     * @return bool
     */
    public static function hasActiveAgent(?int $exceptionId = null, ?int $branchId = null): bool
    {
        return static::withoutGlobalActive()
            ->withOutGlobalBranchPermission()
            ->where(function ($query) use ($exceptionId) {
                $query->where('id', '<>', $exceptionId);
            })
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->exists();
    }

    /** @inheritDoc */
    public function allowedFilterKeys(): array
    {
        return [
            "search",
            "from",
            "to",
            self::ACTIVE_COLUMN_NAME,
            self::BRANCH_COLUMN_NAME,
        ];
    }

    /**
     * Get connection URL.
     *
     * Builds the agent URL from a validated host and standalone port.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        $scheme = 'http';
        $host = rtrim(trim((string)$this->host), '/');

        return sprintf('%s://%s:%d', $scheme, $host, (int)$this->port);
    }

    /** @inheritDoc */
    protected function getSortableAttributes(): array
    {
        return [
            "name",
            "host",
            "port",
            self::ACTIVE_COLUMN_NAME,
            self::BRANCH_COLUMN_NAME,
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
            'api_key' => "encrypted",
            'port' => "integer",
            self::ACTIVE_COLUMN_NAME => "boolean",
        ];
    }
}

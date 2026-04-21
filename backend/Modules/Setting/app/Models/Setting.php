<?php

namespace Modules\Setting\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Setting\Events\SettingSaved;
use Modules\Support\Eloquent\Model;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'is_translatable', 'is_encryptable', 'payload'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => SettingSaved::class,
    ];

    /**
     * Get all settings with cache support.
     *
     * @return Collection
     */
    public static function allCached(): Collection
    {
        try {
            return Cache::tags('settings')->rememberForever(
                makeCacheKey(['settings', 'list']),
                fn() => self::all()->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->payload];
                })
            );
        } catch (QueryException) {
            return collect();
        }
    }
    

    /**
     * Determine if the given setting key exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Set the given settings.
     *
     * @param array $settings
     * @return void
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $payload) {
            self::set($key, $payload);
        }
    }

    /**
     * Set the given setting.
     *
     * @param string $key
     * @param mixed $payload
     * @return void
     */
    public static function set(string $key, mixed $payload): void
    {
        if ($key === 'encryptable') {
            static::setEncryptableSettings($payload);
        } elseif ($key === 'translatable') {
            static::setTranslatableSettings($payload);
        } else {
            static::updateOrCreate(['key' => $key], ['payload' => $payload]);
        }
    }

    /**
     * Set an encryptable settings.
     *
     * @param array $settings
     * @return void
     */
    public static function setEncryptableSettings(array $settings = []): void
    {
        foreach ($settings as $key => $payload) {
            if ($key === 'translatable') {
                static::setTranslatableSettings($payload, true);
            } else {
                static::updateOrCreate(['key' => $key], ['is_encryptable' => true, 'payload' => $payload]);
            }
        }
    }

    /**
     * Set a translatable settings.
     *
     * @param array $settings
     * @param bool $encryptable
     *
     * @return void
     */
    public static function setTranslatableSettings(array $settings = [], bool $encryptable = false): void
    {
        foreach ($settings as $key => $payload) {
            $oldSetting = static::where('key', $key)->first();
            $oldPayload = $oldSetting ? unserialize($oldSetting->getRawOriginal('payload')) : null;
            $oldPayload = is_array($oldPayload) ? $oldPayload : [];
            static::updateOrCreate(['key' => $key], [
                'is_translatable' => true,
                'is_encryptable' => $encryptable,
                'payload' => array_merge($oldPayload, [locale() => $payload]),
            ]);
        }
    }

    /**
     * Get setting for the given key.
     *
     * @param string $key
     * @param mixed $default
     * @return string|array|null
     */
    public static function get(string $key, mixed $default = null): string|array|null
    {
        try {
            return static::where('key', $key)->first()?->payload ?: $default;
        } catch (QueryException) {
            return $default;
        }
    }

    /**
     * Set the payload of the setting.
     *
     * @return Attribute
     */
    public function payload(): Attribute
    {
        return Attribute::make(
            get: function ($payload) {
                if ($this->is_encryptable) {
                    try {
                        $payload = decrypt($payload);
                    } catch (DecryptException|MissingAppKeyException) {
                        $payload = null;
                    }
                } else {
                    $payload = unserialize($payload);
                }

                if ($this->is_translatable) {
                    $fallbackLocale = fallbackLocale();
                    $payload = isset($payload[locale()])
                        ? $payload[locale()]
                        : ($payload[$fallbackLocale] ?? null);
                }

                return $payload;
            },
            set: function ($payload) {
                if ($this->is_encryptable) {
                    return encrypt($payload);
                } else {
                    return serialize($payload);
                }
            }
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_translatable' => 'boolean',
            'is_encryptable' => 'boolean',
        ];
    }
}

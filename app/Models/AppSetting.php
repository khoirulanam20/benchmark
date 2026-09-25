<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    private const SECRET_KEYS = [
        'typesafe_api_key',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();

        if ($row === null || $row->value === null || $row->value === '') {
            return $default;
        }

        if (in_array($key, self::SECRET_KEYS, true)) {
            try {
                return Crypt::decryptString($row->value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $row->value;
    }

    public static function set(string $key, ?string $value): void
    {
        if ($value === null || $value === '') {
            static::query()->where('key', $key)->delete();

            return;
        }

        $stored = in_array($key, self::SECRET_KEYS, true)
            ? Crypt::encryptString($value)
            : $value;

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $stored],
        );
    }

    public static function hasSecret(string $key): bool
    {
        return static::get($key) !== null;
    }
}

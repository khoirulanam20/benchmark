<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'provider_name',
        'encrypted_key',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setKeyAttribute(string $value): void
    {
        $this->attributes['encrypted_key'] = Crypt::encryptString($value);
    }

    public function getDecryptedKeyAttribute(): string
    {
        return Crypt::decryptString($this->attributes['encrypted_key']);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}

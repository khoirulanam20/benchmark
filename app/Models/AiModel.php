<?php

namespace App\Models;

use Database\Factories\AiModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AiModel extends Model
{
    /** @use HasFactory<AiModelFactory> */
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'user_id',
        'model_name',
        'provider',
        'display_name',
        'input_price_per_1k_tokens',
        'output_price_per_1k_tokens',
        'default_params',
        'is_active',
        'encrypted_api_key',
        'base_url',
    ];

    protected function casts(): array
    {
        return [
            'input_price_per_1k_tokens' => 'decimal:6',
            'output_price_per_1k_tokens' => 'decimal:6',
            'default_params' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function benchmarkResults(): HasMany
    {
        return $this->hasMany(BenchmarkResult::class, 'model_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->display_name ?? ($this->provider.'/'.$this->model_name);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePresets($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAvailableToUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhereNull('user_id');
        });
    }

    public function isPreset(): bool
    {
        return $this->user_id === null;
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function setApiKeyAttribute(string $value): void
    {
        $this->attributes['encrypted_api_key'] = Crypt::encryptString($value);
    }

    public function getDecryptedApiKeyAttribute(): ?string
    {
        if (empty($this->attributes['encrypted_api_key'])) {
            return null;
        }

        return Crypt::decryptString($this->attributes['encrypted_api_key']);
    }

    public function hasApiKey(): bool
    {
        return ! empty($this->attributes['encrypted_api_key']);
    }

    public function getEffectiveBaseUrlAttribute(): ?string
    {
        return $this->base_url ?? match ($this->provider) {
            'openai' => 'https://api.openai.com',
            'anthropic' => 'https://api.anthropic.com',
            'google' => 'https://generativelanguage.googleapis.com',
            default => null,
        };
    }
}

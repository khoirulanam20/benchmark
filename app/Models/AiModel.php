<?php

namespace App\Models;

use Database\Factories\AiModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiModel extends Model
{
    /** @use HasFactory<AiModelFactory> */
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'model_name',
        'provider',
        'display_name',
        'input_price_per_1k_tokens',
        'output_price_per_1k_tokens',
        'default_params',
        'is_active',
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
}

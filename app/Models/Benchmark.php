<?php

namespace App\Models;

use App\Enums\BenchmarkStatus;
use Database\Factories\BenchmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Benchmark extends Model
{
    /** @use HasFactory<BenchmarkFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'prompt_text',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BenchmarkStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(BenchmarkResult::class);
    }

    public function getDurationSecondsAttribute(): ?float
    {
        if ($this->started_at === null || $this->completed_at === null) {
            return null;
        }

        return $this->started_at->floatDiffInSeconds($this->completed_at);
    }
}

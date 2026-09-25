<?php

namespace App\Models;

use App\Enums\ScoreStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchmarkResult extends Model
{
    protected $fillable = [
        'benchmark_id',
        'model_id',
        'output_content',
        'prompt_tokens',
        'completion_tokens',
        'cost',
        'latency_seconds',
        'quality_score',
        'score_breakdown',
        'score_status',
        'validated_quality_score',
        'validated_by',
        'validated_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'cost' => 'decimal:8',
            'latency_seconds' => 'float',
            'quality_score' => 'float',
            'score_breakdown' => 'array',
            'score_status' => ScoreStatus::class,
            'validated_quality_score' => 'float',
            'validated_at' => 'datetime',
        ];
    }

    public function benchmark(): BelongsTo
    {
        return $this->belongsTo(Benchmark::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getEffectiveScoreAttribute(): ?float
    {
        return $this->validated_quality_score ?? $this->quality_score;
    }

    public function getTotalTokensAttribute(): int
    {
        return $this->prompt_tokens + $this->completion_tokens;
    }
}

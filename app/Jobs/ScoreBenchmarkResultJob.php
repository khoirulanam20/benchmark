<?php

namespace App\Jobs;

use App\Models\BenchmarkResult;
use App\Services\ScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScoreBenchmarkResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public BenchmarkResult $result,
    ) {}

    public function handle(ScoringService $scoringService): void
    {
        $scoringService->score($this->result);
    }
}

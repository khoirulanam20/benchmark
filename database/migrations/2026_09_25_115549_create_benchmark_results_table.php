<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benchmark_id')->constrained()->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
            $table->text('output_content')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('cost', 14, 8)->default(0);
            $table->float('latency_seconds')->nullable();
            $table->float('quality_score')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->string('score_status', 20)->default('pending');
            $table->float('validated_quality_score')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('benchmark_id');
            $table->index('model_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_results');
    }
};

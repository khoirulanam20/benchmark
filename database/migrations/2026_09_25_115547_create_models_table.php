<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->string('model_name', 100)->unique();
            $table->string('provider', 50);
            $table->string('display_name', 100)->nullable();
            $table->decimal('input_price_per_1k_tokens', 10, 6)->default(0);
            $table->decimal('output_price_per_1k_tokens', 10, 6)->default(0);
            $table->json('default_params')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('provider');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('models');
    }
};

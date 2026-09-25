<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Add monthly cost quota to users
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('monthly_cost_limit', 10, 4)->default(100.0000)->after('role');
            $table->decimal('current_month_cost', 12, 6)->default(0)->after('monthly_cost_limit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['monthly_cost_limit', 'current_month_cost']);
        });
    }
};

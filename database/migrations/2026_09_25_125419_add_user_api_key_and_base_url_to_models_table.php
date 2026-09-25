<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->text('encrypted_api_key')->nullable()->after('default_params');
            $table->string('base_url', 500)->nullable()->after('encrypted_api_key');
            $table->dropUnique('models_model_name_unique');
            $table->unique(['user_id', 'model_name']);
        });
    }

    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropUnique('models_user_id_model_name_unique');
            $table->dropColumn(['user_id', 'encrypted_api_key', 'base_url']);
            $table->unique('model_name');
        });
    }
};

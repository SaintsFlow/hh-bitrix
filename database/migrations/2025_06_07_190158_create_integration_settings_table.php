<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'crm', 'telegram', 'webhook', etc.
            $table->string('name'); // Название интеграции
            $table->boolean('is_active')->default(true);
            $table->json('settings'); // Настройки в JSON формате
            $table->json('webhook_config')->nullable(); // Конфигурация вебхука
            $table->string('webhook_url')->nullable(); // URL вебхука
            $table->string('webhook_method')->default('POST'); // HTTP метод
            $table->json('webhook_headers')->nullable(); // Заголовки для вебхука
            $table->timestamps();

            $table->index(['client_id', 'type']);
            $table->index(['client_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};

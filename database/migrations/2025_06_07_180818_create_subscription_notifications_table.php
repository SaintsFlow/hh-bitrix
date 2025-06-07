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
        Schema::create('subscription_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'warning', 'expired'
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['client_id', 'is_read']);
            $table->index(['type', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_notifications');
    }
};

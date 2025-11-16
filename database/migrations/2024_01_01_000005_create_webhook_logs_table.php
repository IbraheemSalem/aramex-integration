<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id')->nullable()->index();
            $table->foreignId('shipment_id')->nullable()->constrained('aramex_shipments')->onDelete('set null');
            $table->string('event_type')->nullable()->index();
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('processed')->default(false)->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'processed']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};


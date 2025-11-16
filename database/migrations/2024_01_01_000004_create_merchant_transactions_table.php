<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id')->index();
            $table->foreignId('billing_id')->nullable()->constrained('merchant_billings')->onDelete('set null');
            $table->foreignId('shipment_id')->nullable()->constrained('aramex_shipments')->onDelete('set null');
            $table->enum('transaction_type', ['shipment_fee', 'monthly_subscription', 'refund'])->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('SAR');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['merchant_id', 'transaction_type']);
            $table->index(['merchant_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_transactions');
    }
};


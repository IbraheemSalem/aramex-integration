<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_billings', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id')->index();
            $table->string('billing_period', 7)->index(); // YYYY-MM
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('monthly_subscription_fee', 10, 2)->default(0);
            $table->unsignedInteger('total_shipments')->default(0);
            $table->unsignedInteger('free_shipments_used')->default(0);
            $table->unsignedInteger('paid_shipments')->default(0);
            $table->decimal('per_shipment_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['merchant_id', 'billing_period']);
            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_billings');
    }
};


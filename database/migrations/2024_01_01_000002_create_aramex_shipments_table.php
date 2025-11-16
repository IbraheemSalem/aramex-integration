<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aramex_shipments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('merchant_id')->index();
            $table->foreignId('merchant_aramex_account_id')->constrained('merchant_aramex_accounts')->onDelete('cascade');
            $table->string('aramex_shipment_id')->nullable();
            $table->string('aramex_shipment_number')->nullable();
            $table->string('tracking_number')->nullable()->index();
            $table->string('reference')->nullable()->index();
            $table->text('label_url')->nullable();
            $table->string('label_path')->nullable();
            $table->string('status')->default('created')->index();
            $table->json('shipment_data')->nullable();
            $table->json('aramex_response')->nullable();
            $table->json('tracking_data')->nullable();
            $table->json('webhook_data')->nullable();
            $table->json('receiver_info')->nullable();
            $table->json('shipper_info')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('cod_amount', 10, 2)->nullable();
            $table->decimal('rate_amount', 10, 2)->nullable();
            $table->string('product_name')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('last_tracking_update')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['merchant_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aramex_shipments');
    }
};


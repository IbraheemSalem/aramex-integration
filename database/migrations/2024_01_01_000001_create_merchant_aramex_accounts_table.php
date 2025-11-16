<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_aramex_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id')->index();
            $table->string('merchant_api_key', 64)->unique();
            $table->string('aramex_username');
            $table->string('aramex_password');
            $table->string('account_number');
            $table->string('account_pin');
            $table->string('entity');
            $table->string('country_code', 2);
            $table->string('city');
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->boolean('is_active')->default(false)->index();
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['merchant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_aramex_accounts');
    }
};


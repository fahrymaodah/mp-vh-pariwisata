<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('guest_no', 20)->unique();
            $table->string('type', 20)->default('individual');
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('title', 50)->nullable();
            $table->string('company_title', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 10)->nullable();
            $table->string('nationality', 10)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('id_card_no', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->foreignId('master_company_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->foreignId('main_segment_id')->nullable()->constrained('segments')->nullOnDelete();
            $table->foreignId('sales_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('price_code', 20)->nullable();
            $table->decimal('discount', 5, 2)->default(0);
            $table->string('source_booking', 100)->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->text('comments')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_blacklisted')->default(false);
            $table->date('expired_date')->nullable();
            $table->timestamps();

            $table->index(['name', 'first_name']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};

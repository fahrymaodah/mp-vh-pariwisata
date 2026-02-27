<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lookup tables for sales
        Schema::create('activity_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('sales_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('probability')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sales_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_referral_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_competitors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->integer('priority')->default(0);
            $table->string('competitor')->nullable();
            $table->date('next_action_date')->nullable();
            $table->time('next_action_time')->nullable();
            $table->boolean('is_finished')->default(false);
            $table->date('finish_date')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->string('contact_name')->nullable();
            $table->string('prospect_name');
            $table->foreignId('stage_id')->nullable()->constrained('sales_stages')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('sales_products')->nullOnDelete();
            $table->string('status', 20)->default('open');
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->integer('probability')->default(0);
            $table->date('finish_date')->nullable();
            $table->foreignId('reason_id')->nullable()->constrained('sales_reasons')->nullOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('sales_referral_sources')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_code_id')->nullable()->constrained('activity_codes')->nullOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->integer('priority')->default(0);
            $table->string('scheduled_with')->nullable();
            $table->text('regarding')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_opportunity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->string('result', 10)->nullable();
            $table->text('result_notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_tasks');
        Schema::dropIfExists('sales_schedules');
        Schema::dropIfExists('sales_opportunities');
        Schema::dropIfExists('sales_activities');
        Schema::dropIfExists('sales_competitors');
        Schema::dropIfExists('sales_referral_sources');
        Schema::dropIfExists('sales_reasons');
        Schema::dropIfExists('sales_products');
        Schema::dropIfExists('sales_stages');
        Schema::dropIfExists('activity_codes');
    }
};

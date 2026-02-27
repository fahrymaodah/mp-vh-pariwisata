<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrangements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('description');
            $table->string('invoice_label');
            $table->foreignId('lodging_article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->foreignId('arrangement_article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->integer('min_stay')->default(0);
            $table->string('currency_code', 10)->default('IDR');
            $table->text('comments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('arrangement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrangement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('posting_type', 20)->default('daily');
            $table->integer('total_posting')->nullable();
            $table->boolean('included_in_room_rate')->default(true);
            $table->boolean('qty_always_one')->default(true);
            $table->string('guest_type', 10)->default('adult');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrangement_lines');
        Schema::dropIfExists('arrangements');
    }
};

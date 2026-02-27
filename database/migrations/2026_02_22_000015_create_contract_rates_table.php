<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_rates', function (Blueprint $table) {
            $table->id();
            $table->string('price_code', 20);
            $table->string('description');
            $table->string('currency_code', 10)->default('IDR');
            $table->foreignId('room_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('arrangement_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('day_of_week')->default(0);
            $table->integer('adults')->default(1);
            $table->decimal('room_rate', 15, 2);
            $table->decimal('child1_rate', 15, 2)->default(0);
            $table->decimal('child2_rate', 15, 2)->default(0);
            $table->timestamps();

            $table->index('price_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_rates');
    }
};

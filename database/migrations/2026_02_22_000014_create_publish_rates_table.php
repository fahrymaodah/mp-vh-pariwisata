<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('arrangement_id')->constrained()->cascadeOnDelete();
            $table->integer('day_of_week')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rate_single', 15, 2);
            $table->decimal('rate_double', 15, 2);
            $table->decimal('rate_triple', 15, 2)->nullable();
            $table->decimal('rate_quad', 15, 2)->nullable();
            $table->decimal('extra_child1', 15, 2)->default(0);
            $table->decimal('extra_child2', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_rates');
    }
};

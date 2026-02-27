<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('month');
            $table->decimal('lodging', 15, 2)->default(0);
            $table->decimal('fb', 15, 2)->default(0);
            $table->decimal('others', 15, 2)->default(0);
            $table->integer('room_nights')->default(0);
            $table->timestamps();
        });

        Schema::create('segment_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('budget_rooms')->default(0);
            $table->integer('budget_persons')->default(0);
            $table->decimal('budget_lodging', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segment_budgets');
        Schema::dropIfExists('sales_budgets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linen_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('linen_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linen_type_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10);
            $table->integer('qty');
            $table->date('date');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linen_transactions');
        Schema::dropIfExists('linen_types');
    }
};

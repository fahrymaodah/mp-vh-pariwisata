<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 10)->unique();
            $table->foreignId('room_category_id')->constrained()->cascadeOnDelete();
            $table->integer('floor');
            $table->string('status', 30)->default('vacant_clean');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_smoking')->default(false);
            $table->string('overlook', 100)->nullable();
            $table->string('connecting_room', 10)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('floor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

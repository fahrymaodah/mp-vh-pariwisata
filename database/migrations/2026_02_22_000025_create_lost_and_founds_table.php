<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_and_founds', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->default('found');
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->string('found_by');
            $table->string('submitted_to')->nullable();
            $table->string('claimed_by')->nullable();
            $table->date('claimed_date')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_and_founds');
    }
};

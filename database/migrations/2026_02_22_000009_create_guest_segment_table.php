<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_segment', function (Blueprint $table) {
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_main')->default(false);

            $table->primary(['guest_id', 'segment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_segment');
    }
};

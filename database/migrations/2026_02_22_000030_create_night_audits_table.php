<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_audits', function (Blueprint $table) {
            $table->id();
            $table->date('audit_date')->unique();
            $table->string('status', 20)->default('pending');
            $table->json('checklist')->nullable();
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->integer('total_rooms_occupied')->default(0);
            $table->integer('total_rooms_available')->default(0);
            $table->decimal('occupancy_rate', 5, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_audits');
    }
};

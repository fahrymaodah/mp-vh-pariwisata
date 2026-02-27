<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Scenarios ────────────────────────────────
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('module'); // fo, hk, sales, telop
            $table->string('difficulty')->default('beginner'); // beginner, intermediate, advanced
            $table->text('instructions')->nullable();
            $table->json('objectives')->nullable(); // ["Check in a walk-in guest", ...]
            $table->json('initial_data')->nullable(); // snapshot config for reset
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('scenario_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scenario_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->string('status')->default('assigned'); // assigned, in_progress, completed
            $table->json('completed_objectives')->nullable(); // [0 => true, 1 => false, ...]
            $table->integer('score')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['scenario_id', 'user_id']);
        });

        // ── Activity Logs ────────────────────────────
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // created, updated, deleted, checked_in, etc.
            $table->string('module')->nullable(); // fo, hk, sales, telop, admin
            $table->text('description');
            $table->nullableMorphs('loggable'); // polymorphic to any model
            $table->json('metadata')->nullable(); // extra context
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'created_at']);
        });

        // ── Tutorials ────────────────────────────────
        Schema::create('tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('module'); // fo, hk, sales, telop
            $table->string('target_page')->nullable(); // e.g. "quick-check-in", "reservation"
            $table->text('description')->nullable();
            $table->json('steps'); // [{title, content, element?, placement?}, ...]
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tutorial_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutorial_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('current_step')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['tutorial_id', 'user_id']);
        });

        // ── Quizzes ──────────────────────────────────
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('module'); // fo, hk, sales, telop
            $table->text('description')->nullable();
            $table->integer('passing_score')->default(70); // percentage
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->string('type')->default('multiple_choice'); // multiple_choice, true_false
            $table->json('options')->nullable(); // ["Option A", "Option B", ...] null for true/false
            $table->string('correct_answer'); // index or value
            $table->text('explanation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('answers')->nullable(); // {question_id: selected_answer, ...}
            $table->integer('score')->nullable(); // percentage
            $table->boolean('passed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('tutorial_progress');
        Schema::dropIfExists('tutorials');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('scenario_assignments');
        Schema::dropIfExists('scenarios');
    }
};

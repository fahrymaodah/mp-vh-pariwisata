<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('extension_no', 20)->nullable();
            $table->string('dialed_number', 50);
            $table->date('call_date');
            $table->string('call_time', 8)->nullable();
            $table->integer('duration')->default(0)->comment('seconds');
            $table->string('call_type', 20)->default('outgoing')->comment('outgoing, incoming, internal');
            $table->decimal('rate_amount', 12, 2)->default(0);
            $table->boolean('is_posted')->default(false);
            $table->string('posted_to_bill')->nullable()->comment('guest, non-stay, master');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['call_date', 'extension_no']);
            $table->index('is_posted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};

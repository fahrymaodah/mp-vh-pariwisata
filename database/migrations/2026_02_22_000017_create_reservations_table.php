<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_no', 20)->unique();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->string('group_name')->nullable();
            $table->foreignId('segment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reserved_by')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('status', 20)->default('confirmed');
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->integer('nights');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->string('children_ages', 100)->nullable();
            $table->boolean('is_complimentary')->default(false);
            $table->foreignId('room_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('room_qty')->default(1);
            $table->foreignId('arrangement_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('room_rate', 15, 2);
            $table->string('currency_code', 10)->default('IDR');
            $table->boolean('is_fix_rate')->default(false);
            $table->text('bill_instruction')->nullable();
            $table->string('purpose')->nullable();
            $table->string('flight_no', 50)->nullable();
            $table->time('eta')->nullable();
            $table->time('etd')->nullable();
            $table->boolean('is_pickup')->default(false);
            $table->boolean('is_dropoff')->default(false);
            $table->text('comments')->nullable();
            $table->string('letter_no', 50)->nullable();
            $table->decimal('ta_commission', 15, 2)->default(0);
            $table->date('deposit_limit_date')->nullable();
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->decimal('deposit_paid', 15, 2)->default(0);
            $table->decimal('deposit2_paid', 15, 2)->default(0);
            $table->decimal('deposit_balance', 15, 2)->default(0);
            $table->boolean('is_master_bill')->default(false);
            $table->string('master_bill_receiver')->nullable();
            $table->boolean('is_incognito')->default(false);
            $table->boolean('is_day_use')->default(false);
            $table->boolean('is_room_sharer')->default(false);
            $table->foreignId('parent_reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('arrival_date');
            $table->index('departure_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

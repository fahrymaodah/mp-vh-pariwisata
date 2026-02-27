<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Make reservation_id nullable for Non-Stay Guest invoices
            $table->foreignId('reservation_id')->nullable()->change();

            // Add department_id for NSG invoices (must select department first)
            $table->foreignId('department_id')->nullable()->after('room_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            $table->foreignId('reservation_id')->nullable(false)->change();
        });
    }
};

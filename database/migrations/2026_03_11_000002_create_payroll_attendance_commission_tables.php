<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff profiles — extends users with payroll info
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_code')->unique()->nullable();
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('join_date')->nullable();
            // Payroll
            $table->double('base_salary')->default(0);
            $table->enum('salary_type', ['fixed', 'base_plus_commission'])->default('fixed');
            // Commission rates (% of order total)
            $table->double('packing_commission_rate')->default(0)->comment('% of order total for packing');
            $table->double('delivery_commission_rate')->default(0)->comment('% of order total for delivery');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Daily attendance
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'half_day', 'leave'])->default('present');
            $table->text('note')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'date']);
        });

        // Payroll runs (monthly)
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month'); // e.g. "2026-03"
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('half_days')->default(0);
            $table->double('base_salary')->default(0);
            $table->double('packing_commission')->default(0);
            $table->double('delivery_commission')->default(0);
            $table->double('bonus')->default(0);
            $table->double('deductions')->default(0);
            $table->double('net_salary')->default(0);
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'month']);
        });

        // Link orders to packer + deliverer staff
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['packed_by']);
            $table->dropForeign(['delivered_by']);
            $table->dropColumn(['packed_by', 'delivered_by']);
        });
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('staff_profiles');
    }
};

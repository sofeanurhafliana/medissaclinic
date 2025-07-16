<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('service');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('branch');
            $table->enum('booking_status', ['pending', 'approved', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};
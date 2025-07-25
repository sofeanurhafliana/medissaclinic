<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctor_unavailabilities', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('doctor_id');
    $table->date('unavailable_date');
    $table->time('unavailable_start');
    $table->time('unavailable_end');
    $table->string('note')->nullable();
    $table->timestamps();

    $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_unavailabilities');
    }
};

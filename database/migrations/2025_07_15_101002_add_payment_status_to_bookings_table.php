<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid'])->default('pending')->after('booking_status');
            $table->decimal('deposit_amount', 8, 2)->default(0.00)->after('payment_status');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'deposit_amount']);
        });
    }
};

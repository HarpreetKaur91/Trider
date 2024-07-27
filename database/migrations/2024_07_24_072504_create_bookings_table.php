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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('id')->on('users')->onDelete('cascade');
            $table->char('order_no');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->decimal('total_amount',2);
            $table->decimal('grand_amount',2);
            $table->decimal('amount',2);
            $table->decimal('tax',2);
            $table->set('payment_status',['cod','online']);
            $table->set('booking_status',['new','in_progress','completed'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

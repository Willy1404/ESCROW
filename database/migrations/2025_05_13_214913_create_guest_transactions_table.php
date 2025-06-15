<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('guest_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_token', 32)->unique();
            $table->string('control_number', 20);
            $table->string('buyer_email')->nullable();
            $table->string('buyer_name')->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('escrow_id', 50)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10);
            $table->string('item_name');
            $table->timestamp('expires_at');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('control_number')->references('control_number')->on('control_numbers');
            $table->foreign('escrow_id')->references('escrow_id')->on('escrow_transactions')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guest_transactions');
    }
}
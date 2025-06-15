<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateControlNumbersTable extends Migration
{
    public function up()
    {
        Schema::create('control_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('control_number', 20)->unique();
            $table->string('seller_id', 50);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('TZS');
            $table->string('item_name');
            $table->string('item_condition')->nullable();
            $table->text('item_description')->nullable();
            $table->date('delivery_deadline');
            $table->integer('inspection_period')->default(7);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->foreign('seller_id')->references('user_id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_numbers');
    }
}
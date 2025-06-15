<?php
// database/migrations/2023_01_01_000003_create_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id', 50)->unique();
            $table->string('escrow_id', 50);
            $table->string('buyer_id', 50);
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50);
            $table->enum('status', ['Pending', 'Completed', 'Failed'])->default('Pending');
            $table->timestamps();
            
            $table->foreign('escrow_id')->references('escrow_id')->on('escrow_transactions');
            $table->foreign('buyer_id')->references('user_id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}

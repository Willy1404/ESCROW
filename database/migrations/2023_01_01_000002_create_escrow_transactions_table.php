<?php
// database/migrations/2023_01_01_000002_create_escrow_transactions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEscrowTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('escrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('escrow_id', 50)->unique();
            $table->string('buyer_id', 50);
            $table->string('seller_id', 50);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10);
            $table->enum('status', [
                'Funds Pending', 
                'Funds Received', 
                'In Transit', 
                'Waiting for Buyer Approval', 
                'Funds Released', 
                'Escrow On Hold'
            ])->default('Funds Pending');
            $table->date('delivery_deadline');
            $table->integer('inspection_period')->default(7); // Days
            $table->json('contract_terms')->nullable();
            $table->timestamps();
            
            $table->foreign('buyer_id')->references('user_id')->on('users');
            $table->foreign('seller_id')->references('user_id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('escrow_transactions');
    }
}
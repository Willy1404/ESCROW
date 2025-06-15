<?php
// database/migrations/2023_01_01_000005_create_disputes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputesTable extends Migration
{
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_id', 50)->unique();
            $table->string('escrow_id', 50);
            $table->string('buyer_id', 50);
            $table->string('seller_id', 50);
            $table->text('reason');
            $table->enum('status', ['Pending', 'Resolved', 'Rejected'])->default('Pending');
            $table->text('resolution')->nullable();
            $table->string('resolved_by', 50)->nullable();
            $table->timestamps();
            
            $table->foreign('escrow_id')->references('escrow_id')->on('escrow_transactions');
            $table->foreign('buyer_id')->references('user_id')->on('users');
            $table->foreign('seller_id')->references('user_id')->on('users');
            $table->foreign('resolved_by')->references('user_id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('disputes');
    }
}
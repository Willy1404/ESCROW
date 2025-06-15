<?php
// database/migrations/2023_01_01_000004_create_shipments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('escrow_id', 50);
            $table->string('tracking_id', 50)->nullable(); // Made nullable
            $table->string('carrier', 100);
            $table->date('estimated_arrival');
            $table->enum('status', ['Pending', 'In Transit', 'Delivered'])->default('Pending');
            $table->timestamps();
            
            $table->foreign('escrow_id')->references('escrow_id')->on('escrow_transactions');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
}
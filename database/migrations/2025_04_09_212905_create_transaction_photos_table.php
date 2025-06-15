<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionPhotosTable extends Migration
{
    public function up()
    {
        Schema::create('transaction_photos', function (Blueprint $table) {
            $table->id();
            $table->string('escrow_id', 50);
            $table->string('uploader_id', 50);
            $table->enum('photo_type', ['shipment_evidence', 'delivery_evidence', 'dispute_evidence']);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->text('description')->nullable();
            $table->string('dispute_id', 50)->nullable();
            $table->timestamps();
            
            $table->foreign('escrow_id')->references('escrow_id')->on('escrow_transactions');
            $table->foreign('uploader_id')->references('user_id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_photos');
    }
}
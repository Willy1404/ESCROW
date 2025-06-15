<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddControlNumberToTransactionPhotosTable extends Migration
{
    public function up()
    {
        Schema::table('transaction_photos', function (Blueprint $table) {
            $table->string('control_number', 20)->nullable()->after('escrow_id');
            $table->foreign('control_number')->references('control_number')->on('control_numbers');
        });
    }

    public function down()
    {
        Schema::table('transaction_photos', function (Blueprint $table) {
            $table->dropForeign(['control_number']);
            $table->dropColumn('control_number');
        });
    }
}
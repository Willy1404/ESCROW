<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingActionsTable extends Migration
{
    public function up()
    {
        Schema::create('pending_actions', function (Blueprint $table) {
            $table->id();
            $table->string('action_id', 50)->unique();
            $table->string('maker_id', 50);
            $table->string('checker_id', 50)->nullable();
            $table->string('action_type');
            $table->text('action_data');
            $table->string('entity_type');
            $table->string('entity_id');
            $table->text('description');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            $table->foreign('maker_id')->references('user_id')->on('users');
            $table->foreign('checker_id')->references('user_id')->on('users')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_actions');
    }
}
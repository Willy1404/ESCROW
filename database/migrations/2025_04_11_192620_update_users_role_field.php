<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUsersRoleField extends Migration
{
    public function up()
    {
        // For MySQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'seller', 'bank_staff', 'it_support') NOT NULL");
        
        
    }

    public function down()
    {
        // For MySQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'seller', 'bank_staff') NOT NULL");
        
        
    }
}
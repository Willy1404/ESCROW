<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUserRolesForMakerChecker extends Migration
{
    public function up()
    {
        // First convert role to VARCHAR to avoid ENUM constraints
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL");
        
        // Now update the values without ENUM constraints
        DB::statement("UPDATE users SET role = 'maker' WHERE role = 'bank_staff'");
        
        // Finally convert back to ENUM with new values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'seller', 'maker', 'checker', 'it_support') NOT NULL");
    }

    public function down()
    {
        // Revert makers and checkers back to bank_staff
        DB::statement("UPDATE users SET role = 'bank_staff' WHERE role IN ('maker', 'checker')");
        
        // For MySQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('buyer', 'seller', 'bank_staff', 'it_support') NOT NULL");
    }
}

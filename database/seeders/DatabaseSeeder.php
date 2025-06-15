<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create bank staff
        User::create([
            'user_id' => 'BNK'.rand(10000, 99999),
            'name' => 'Bank Admin',
            'email' => 'admin@azaniabank.com',
            'role' => 'bank_staff',
            'password' => Hash::make('password'),
        ]);
        
        // Create buyers
        User::create([
            'user_id' => 'BUY'.rand(10000, 99999),
            'name' => 'John Buyer',
            'email' => 'buyer@example.com',
            'role' => 'buyer',
            'password' => Hash::make('password'),
        ]);
        
        // Create sellers
        User::create([
            'user_id' => 'SEL'.rand(10000, 99999),
            'name' => 'Jane Seller',
            'email' => 'seller@example.com',
            'role' => 'seller',
            'password' => Hash::make('password'),
        ]);

        
    }
}
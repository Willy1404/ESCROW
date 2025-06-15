<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ITSupportUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create IT Support user
        User::create([
            'user_id' => 'IT'.rand(10000, 99999),
            'name' => 'IT Support Admin',
            'email' => 'it@azaniabank.com',
            'role' => 'it_support',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        
        // Create Maker user
        User::create([
            'user_id' => 'MKR'.rand(10000, 99999),
            'name' => 'Bank Maker',
            'email' => 'maker@azaniabank.com',
            'role' => 'maker',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        
        // Create Checker user
        User::create([
            'user_id' => 'CHK'.rand(10000, 99999),
            'name' => 'Bank Checker',
            'email' => 'checker@azaniabank.com',
            'role' => 'checker',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        
        $this->command->info('IT Support admin user created:');
        $this->command->info('Email: it@azaniabank.com');
        $this->command->info('Password: password');
        
        $this->command->info('Maker user created:');
        $this->command->info('Email: maker@azaniabank.com');
        $this->command->info('Password: password');
        
        $this->command->info('Checker user created:');
        $this->command->info('Email: checker@azaniabank.com');
        $this->command->info('Password: password');
    }
}
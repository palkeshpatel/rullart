<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('admin')->insert([
            'id' => 1,
            'user' => 'admin',
            'pass' => md5('password'),
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'site' => 0,
            'user_role' => 1,
            'lock_access' => 0,
            'fkstoreid' => 1,
            'created_date' => now(),
        ]);
    }
}

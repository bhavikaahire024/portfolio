<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $pollId = DB::table('polls')->insertGetId([
            'question' => 'Which feature matters most in a live poll?',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $options = [
            'Real-time updates',
            'IP restriction',
            'Admin moderation',
            'Clean UI',
        ];

        foreach ($options as $option) {
            DB::table('poll_options')->insert([
                'poll_id' => $pollId,
                'option_text' => $option,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 5 Kasir
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => 'Kasir ' . $i,
                'type' => 'kasir',
            ]);
        }

        // 3 Display
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => 'Display ' . $i,
                'type' => 'display',
            ]);
        }

        Queue::create([
            'id' => 0,
            'queue_number' => 0,
            'type' => 'O',
            'deleted_at' => now(),
        ]);
    }
}

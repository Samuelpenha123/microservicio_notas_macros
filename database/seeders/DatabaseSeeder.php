<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Agente Demo',
            'email' => 'agente@example.com',
        ]);

        $this->call([
            MacroSeeder::class,
            InternalNoteSeeder::class,
        ]);
    }
}

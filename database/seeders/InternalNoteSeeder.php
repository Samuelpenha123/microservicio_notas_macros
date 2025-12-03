<?php

namespace Database\Seeders;

use App\Models\InternalNote;
use Illuminate\Database\Seeder;

class InternalNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InternalNote::factory()->count(10)->create();
    }
}

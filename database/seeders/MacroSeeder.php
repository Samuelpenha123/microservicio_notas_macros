<?php

namespace Database\Seeders;

use App\Models\Macro;
use Illuminate\Database\Seeder;

class MacroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Macro::factory()->count(5)->create();

        Macro::create([
            'name' => 'Saludo inicial',
            'content' => "Hola {{nombre}},\nGracias por contactarnos. Ya reviso tu caso.",
            'scope' => 'global',
            'created_by' => 1,
        ]);

        Macro::create([
            'name' => 'Solicitud de detalles',
            'content' => "¿Podrías compartir capturas o pasos exactos para replicar el problema?",
            'scope' => 'global',
            'created_by' => 1,
        ]);

        Macro::create([
            'name' => 'Cierre amable',
            'content' => "Damos por cerrado el ticket. Si necesitas algo adicional, responde a este mismo hilo.",
            'scope' => 'personal',
            'created_by' => 1,
        ]);
    }
}

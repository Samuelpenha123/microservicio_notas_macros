<?php

namespace App\Services;

use App\Models\Macro;

class MacroRenderer
{
    public function render(Macro $macro, array $context = []): string
    {
        $content = $macro->content;

        return (string) preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) use ($context) {
            $key = strtolower(str_replace(' ', '', $matches[1] ?? ''));

            if ($key === '') {
                return $matches[0];
            }

            $value = data_get($context, $key);

            if ($value === null) {
                return $matches[0];
            }

            return (string) $value;
        }, $content);
    }

    public function placeholders(): array
    {
        return [
            'ticket.code' => 'Código del ticket',
            'ticket.title' => 'Título del ticket',
            'customer.name' => 'Nombre del cliente',
            'agent.name' => 'Nombre del agente',
            'agent.email' => 'Correo del agente',
            'today' => 'Fecha actual (Y-m-d)',
            'now' => 'Fecha y hora actual',
        ];
    }
}

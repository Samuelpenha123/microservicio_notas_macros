<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMacroRequest;
use App\Models\Macro;
use App\Support\ExternalAuth;
use Illuminate\Http\RedirectResponse;

class MacroController extends Controller
{
    public function store(StoreMacroRequest $request): RedirectResponse
    {
        $macro = Macro::create([
            'name' => $request->input('name'),
            'content' => $request->input('content'),
            'scope' => $request->input('scope', 'personal'),
            'created_by' => ExternalAuth::id(),
        ]);

        return back()->with('status', "Macro {$macro->name} creada.");
    }

    public function destroy(Macro $macro): RedirectResponse
    {
        $agentId = ExternalAuth::id();

        if ($macro->created_by !== $agentId) {
            abort(403, 'Solo el autor puede eliminar la macro.');
        }

        $macro->delete();

        return back()->with('status', 'Macro eliminada.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMacroRequest;
use App\Http\Requests\UpdateMacroRequest;
use App\Models\Macro;
use App\Models\MacroFavorite;
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
            'category' => $request->input('category'),
            'created_by' => ExternalAuth::id(),
            'created_by_name' => ExternalAuth::name(),
        ]);

        return back()->with('status', "Macro {$macro->name} creada.");
    }

    public function update(UpdateMacroRequest $request, Macro $macro): RedirectResponse
    {
        $agentId = ExternalAuth::id();

        if ($macro->created_by !== $agentId) {
            abort(403, 'Solo el autor puede editar la macro.');
        }

        $macro->update([
            'name' => $request->input('name'),
            'content' => $request->input('content'),
            'scope' => $request->input('scope', 'personal'),
            'category' => $request->input('category'),
        ]);

        return back()->with('status', "Macro {$macro->name} actualizada.");
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

    public function toggleFavorite(Macro $macro): RedirectResponse
    {
        $agentId = ExternalAuth::id();

        $favorite = MacroFavorite::query()
            ->where('macro_id', $macro->id)
            ->where('agent_id', $agentId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('status', 'Macro removida de favoritos.');
        }

        MacroFavorite::create([
            'macro_id' => $macro->id,
            'agent_id' => $agentId,
        ]);

        return back()->with('status', 'Macro agregada a favoritos.');
    }
}

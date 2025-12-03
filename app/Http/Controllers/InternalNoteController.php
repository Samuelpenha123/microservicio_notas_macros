<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternalNoteRequest;
use App\Models\InternalNote;
use App\Support\ExternalAuth;
use Illuminate\Http\RedirectResponse;

class InternalNoteController extends Controller
{
    public function store(StoreInternalNoteRequest $request): RedirectResponse
    {
        $agentId = ExternalAuth::id();

        InternalNote::create([
            'agent_id' => $agentId,
            'ticket_code' => $request->input('ticket_code'),
            'content' => $request->input('content'),
        ]);

        return back()->with('status', 'Nota interna guardada.');
    }

    public function destroy(InternalNote $internalNote): RedirectResponse
    {
        if ($internalNote->agent_id !== ExternalAuth::id()) {
            abort(403);
        }

        $internalNote->delete();

        return back()->with('status', 'Nota interna eliminada.');
    }
}

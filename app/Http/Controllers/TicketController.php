<?php

namespace App\Http\Controllers;

use App\Exceptions\HelpdeskClientException;
use App\Http\Requests\TicketResponseRequest;
use App\Models\InternalNote;
use App\Models\Macro;
use App\Services\HelpdeskClient;
use App\Support\ExternalAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $client = new HelpdeskClient($request->session()->get('ext_token'));

        try {
            $tickets = $client->tickets();
        } catch (HelpdeskClientException $exception) {
            $tickets = [];
            $request->session()->flash('error', $exception->getMessage());
        }

        return view('tickets.index', [
            'tickets' => $tickets,
            'agent' => ExternalAuth::user(),
        ]);
    }

    public function show(Request $request, string $ticketId): View
    {
        $client = new HelpdeskClient($request->session()->get('ext_token'));
        $ticketCode = (string) $ticketId;

        try {
            $ticket = $client->ticket($ticketCode);
            $responses = $client->ticketResponses($ticketCode);
        } catch (HelpdeskClientException $exception) {
            $status = $exception->getCode() ?: 502;
            abort($status, $exception->getMessage());
        }

        $ticketCode = (string) data_get($ticket, 'ticket_code', $ticketCode);
        $agentId = ExternalAuth::id();

        $notes = InternalNote::query()
            ->ownedBy($agentId)
            ->forTicketCode($ticketCode)
            ->latest()
            ->get();

        $macros = Macro::query()
            ->visibleFor($agentId)
            ->orderByRaw("FIELD(scope, 'global', 'personal')")
            ->orderBy('name')
            ->get();

        return view('tickets.show', [
            'ticket' => $ticket,
            'responses' => $responses,
            'notes' => $notes,
            'macros' => $macros,
            'ticketCode' => $ticketCode,
        ]);
    }

    public function storeResponse(TicketResponseRequest $request, string $ticketId): RedirectResponse
    {
        $client = new HelpdeskClient($request->session()->get('ext_token'));
        $ticketCode = (string) $ticketId;

        try {
            $client->storeTicketResponse($ticketCode, [
                'content' => $request->input('content'),
                'internal' => (bool) $request->boolean('internal', false),
            ]);
        } catch (HelpdeskClientException $exception) {
            return back()->withErrors(['content' => $exception->getMessage()])->withInput();
        }

        return redirect()
            ->route('tickets.show', $ticketCode)
            ->with('status', 'Respuesta enviada al cliente correctamente.');
    }
}

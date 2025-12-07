<?php

namespace App\Http\Controllers;

use App\Exceptions\HelpdeskClientException;
use App\Http\Requests\TicketResponseRequest;
use App\Models\InternalNote;
use App\Models\Macro;
use App\Models\MacroFavorite;
use App\Models\MacroUsage;
use App\Services\HelpdeskClient;
use App\Services\MacroRenderer;
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

        $noteFilters = [
            'search' => $request->input('note_search'),
            'author' => $request->integer('note_author'),
            'important' => $request->boolean('note_only_important'),
            'attachments' => $request->boolean('note_only_with_attachments'),
        ];

        $notes = InternalNote::query()
            ->forTicketCode($ticketCode)
            ->with('attachments')
            ->latest()
            ->search($noteFilters['search'])
            ->byAuthor($noteFilters['author'])
            ->importantOnly($noteFilters['important'])
            ->withAttachmentsOnly($noteFilters['attachments'])
            ->get();

        $noteAuthors = InternalNote::query()
            ->forTicketCode($ticketCode)
            ->select('agent_id', 'agent_name')
            ->distinct()
            ->orderBy('agent_name')
            ->get();

        $favoriteMacroIds = MacroFavorite::query()
            ->where('agent_id', $agentId)
            ->pluck('macro_id')
            ->all();

        $macroFilters = [
            'search' => $request->input('macro_search'),
            'category' => $request->input('macro_category'),
            'author' => $request->integer('macro_author'),
            'favorites' => $request->boolean('macro_only_favorites'),
        ];

        $macrosQuery = Macro::query()
            ->visibleFor($agentId)
            ->orderByRaw("FIELD(scope, 'global', 'personal')")
            ->orderBy('name')
            ->search($macroFilters['search'])
            ->inCategory($macroFilters['category'])
            ->byAuthor($macroFilters['author']);

        if ($macroFilters['favorites']) {
            $macrosQuery->whereIn('id', $favoriteMacroIds ?: [-1]);
        }

        $macros = $macrosQuery->get();

        $macroCategories = Macro::query()
            ->visibleFor($agentId)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $macroAuthors = Macro::query()
            ->visibleFor($agentId)
            ->select('created_by', 'created_by_name')
            ->distinct()
            ->orderBy('created_by_name')
            ->get();

        $macroUsageHistory = MacroUsage::query()
            ->with('macro')
            ->where('agent_id', $agentId)
            ->latest()
            ->limit(10)
            ->get();

        $topMacros = Macro::query()
            ->visibleFor($agentId)
            ->withCount('usages')
            ->orderByDesc('usages_count')
            ->limit(5)
            ->get();

        $macroEffectivenessReport = Macro::query()
            ->visibleFor($agentId)
            ->withCount('usages')
            ->withCount([
                'usages as positive_feedback_count' => function ($query) {
                    $query->where('feedback', 'positive');
                },
            ])
            ->having('usages_count', '>', 0)
            ->orderByRaw('positive_feedback_count / NULLIF(usages_count, 0) DESC')
            ->limit(5)
            ->get();

        $macroPlaceholders = app(MacroRenderer::class)->placeholders();

        return view('tickets.show', [
            'ticket' => $ticket,
            'responses' => $responses,
            'notes' => $notes,
            'macros' => $macros,
            'ticketCode' => $ticketCode,
            'macroFilters' => $macroFilters,
            'macroCategories' => $macroCategories,
            'macroAuthors' => $macroAuthors,
            'favoriteMacroIds' => $favoriteMacroIds,
            'macroUsageHistory' => $macroUsageHistory,
            'topMacros' => $topMacros,
            'macroEffectivenessReport' => $macroEffectivenessReport,
            'macroPlaceholders' => $macroPlaceholders,
            'noteFilters' => $noteFilters,
            'noteAuthors' => $noteAuthors,
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

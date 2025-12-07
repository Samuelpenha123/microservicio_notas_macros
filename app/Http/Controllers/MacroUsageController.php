<?php

namespace App\Http\Controllers;

use App\Models\Macro;
use App\Models\MacroUsage;
use App\Services\MacroRenderer;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MacroUsageController extends Controller
{
    public function preview(Request $request, Macro $macro, MacroRenderer $renderer): JsonResponse
    {
        $this->ensureVisibility($macro);

        $context = $this->buildContext($request);
        $content = $renderer->render($macro, $context);

        return response()->json([
            'data' => [
                'content' => $content,
                'available_placeholders' => $renderer->placeholders(),
            ],
        ]);
    }

    public function store(Request $request, Macro $macro): JsonResponse
    {
        $agentId = $this->ensureVisibility($macro);

        $usage = MacroUsage::create([
            'macro_id' => $macro->id,
            'agent_id' => $agentId,
            'ticket_code' => (string) $request->input('ticket_code'),
            'customized' => (bool) $request->boolean('customized', false),
            'rendered_content' => $request->input('content'),
        ]);

        return response()->json([
            'data' => [
                'usage_id' => $usage->id,
            ],
        ]);
    }

    public function feedback(Request $request, MacroUsage $usage): JsonResponse
    {
        if ($usage->agent_id !== ExternalAuth::id()) {
            abort(403);
        }

        $feedback = $request->input('feedback');

        if (! in_array($feedback, ['positive', 'negative'], true)) {
            abort(422, 'Feedback inválido.');
        }

        $usage->update([
            'feedback' => $feedback,
        ]);

        return response()->json([
            'data' => [
                'feedback' => $feedback,
            ],
        ]);
    }

    protected function buildContext(Request $request): array
    {
        return [
            'ticket' => [
                'code' => (string) $request->input('ticket_code'),
                'title' => (string) $request->input('ticket_title'),
            ],
            'customer' => [
                'name' => (string) $request->input('customer_name'),
            ],
            'agent' => [
                'name' => ExternalAuth::name(),
                'email' => ExternalAuth::email(),
            ],
            'today' => now()->toDateString(),
            'now' => now()->toDayDateTimeString(),
        ];
    }

    protected function ensureVisibility(Macro $macro): int
    {
        $agentId = (int) ExternalAuth::id();

        if ($macro->scope === 'global' || $macro->created_by === $agentId) {
            return $agentId;
        }

        abort(403);
    }
}

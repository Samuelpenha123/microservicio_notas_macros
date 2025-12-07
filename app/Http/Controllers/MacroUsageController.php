<?php

namespace App\Http\Controllers;

use App\Models\Macro;
use App\Models\MacroUsage;
use App\Services\MacroRenderer;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *      schema="MacroUsage",
 *      required={"macro_id", "agent_id", "ticket_code", "rendered_content"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int64"
 *      ),
 *      @OA\Property(
 *          property="macro_id",
 *          description="Macro ID",
 *          type="integer",
 *          format="int64"
 *      ),
 *      @OA\Property(
 *          property="agent_id",
 *          description="Agent ID",
 *          type="integer",
 *          format="int64"
 *      ),
 *      @OA\Property(
 *          property="ticket_code",
 *          description="Ticket Code",
 *          type="string"
 *      ),
 *       @OA\Property(
 *          property="rendered_content",
 *          description="Rendered Content",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="customized",
 *          description="Is Customized",
 *          type="boolean"
 *      ),
 *      @OA\Property(
 *          property="feedback",
 *          description="Feedback (positive/negative)",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Created At",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Updated At",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class MacroUsageController extends Controller
{
    /**
     * @OA\Post(
     *      path="/macros/{macro}/preview",
     *      operationId="previewMacro",
     *      tags={"Macro Usage"},
     *      summary="Preview a macro with context",
     *      description="Returns rendered content of a macro",
     *      @OA\Parameter(
     *          name="macro",
     *          description="Macro ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"ticket_code", "ticket_title", "customer_name"},
     *              @OA\Property(property="ticket_code", type="string", example="T-12345"),
     *              @OA\Property(property="ticket_title", type="string", example="Issue with login"),
     *              @OA\Property(property="customer_name", type="string", example="John Doe")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="content", type="string"),
     *                  @OA\Property(property="available_placeholders", type="array", @OA\Items(type="string"))
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/macros/{macro}/usages",
     *      operationId="storeMacroUsage",
     *      tags={"Macro Usage"},
     *      summary="Record macro usage",
     *      description="Stores a record of macro usage",
     *      @OA\Parameter(
     *          name="macro",
     *          description="Macro ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"ticket_code", "content"},
     *              @OA\Property(property="ticket_code", type="string", example="T-12345"),
     *              @OA\Property(property="content", type="string", example="Rendered content..."),
     *              @OA\Property(property="customized", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="usage_id", type="integer")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/macro-usages/{macroUsage}/feedback",
     *      operationId="feedbackMacroUsage",
     *      tags={"Macro Usage"},
     *      summary="Provide feedback on macro usage",
     *      description="Updates feedback for a macro usage",
     *      @OA\Parameter(
     *          name="macroUsage",
     *          description="Macro Usage ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"feedback"},
     *              @OA\Property(property="feedback", type="string", enum={"positive", "negative"})
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="feedback", type="string")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid feedback"
     *      )
     * )
     */
    public function feedback(Request $request, MacroUsage $usage): JsonResponse
    {
        if ($usage->agent_id !== ExternalAuth::id()) {
            abort(403);
        }

        $feedback = $request->input('feedback');

        if (!in_array($feedback, ['positive', 'negative'], true)) {
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

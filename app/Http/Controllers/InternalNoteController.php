<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternalNoteRequest;
use App\Http\Requests\UpdateInternalNoteRequest;
use App\Models\InternalNote;
use App\Support\ExternalAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class InternalNoteController extends Controller
{
    /**
     * @OA\Post(
     *      path="/internal-notes",
     *      operationId="storeInternalNote",
     *      tags={"Internal Notes"},
     *      summary="Store new internal note",
     *      description="Creates a new internal note",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"ticket_code", "content"},
     *              @OA\Property(property="ticket_code", type="string", example="T-12345"),
     *              @OA\Property(property="content", type="string", example="Note content"),
     *              @OA\Property(property="is_important", type="boolean", example=false),
     *              @OA\Property(property="attachments", type="array", @OA\Items(type="string", format="binary"))
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="data", ref="#/components/schemas/InternalNote")
     *          )
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     */
    public function store(StoreInternalNoteRequest $request): RedirectResponse|JsonResponse
    {
        $agentId = ExternalAuth::id();
        $agentName = ExternalAuth::name();
        $agentEmail = ExternalAuth::email();
        $content = (string) $request->input('content');
        $mentions = $this->extractMentions($content);

        $note = InternalNote::create([
            'agent_id' => $agentId,
            'agent_name' => $agentName,
            'agent_email' => $agentEmail,
            'ticket_code' => $request->input('ticket_code'),
            'content' => $content,
            'is_important' => $request->boolean('is_important'),
            'mentions' => $mentions,
        ]);

        $this->storeAttachments($note, $request->file('attachments', []));
        $this->syncMentions($note, $mentions, $agentId);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'Nota interna guardada.',
                'data' => $note->load('attachments'),
            ]);
        }

        return back()->with('status', 'Nota interna guardada.');
    }

    /**
     * @OA\Put(
     *      path="/internal-notes/{internalNote}",
     *      operationId="updateInternalNote",
     *      tags={"Internal Notes"},
     *      summary="Update internal note",
     *      description="Updates an existing internal note",
     *      @OA\Parameter(
     *          name="internalNote",
     *          description="Internal Note ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"content"},
     *              @OA\Property(property="content", type="string", example="Updated content"),
     *              @OA\Property(property="is_important", type="boolean", example=false),
     *               @OA\Property(property="attachments", type="array", @OA\Items(type="string", format="binary"))
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="data", ref="#/components/schemas/InternalNote")
     *          )
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     */
    public function update(UpdateInternalNoteRequest $request, InternalNote $internalNote): RedirectResponse|JsonResponse
    {
        if ($internalNote->agent_id !== ExternalAuth::id()) {
            abort(403);
        }

        $content = (string) $request->input('content');
        $mentions = $this->extractMentions($content);

        $internalNote->update([
            'content' => $content,
            'is_important' => $request->boolean('is_important'),
            'mentions' => $mentions,
        ]);

        $this->storeAttachments($internalNote, $request->file('attachments', []));
        $this->syncMentions($internalNote, $mentions, $internalNote->agent_id, true);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'Nota interna actualizada.',
                'data' => $internalNote->load('attachments'),
            ]);
        }

        return back()->with('status', 'Nota interna actualizada.');
    }

    /**
     * @OA\Delete(
     *      path="/internal-notes/{internalNote}",
     *      operationId="deleteInternalNote",
     *      tags={"Internal Notes"},
     *      summary="Delete internal note",
     *      description="Deletes an internal note",
     *      @OA\Parameter(
     *          name="internalNote",
     *          description="Internal Note ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string")
     *          )
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function destroy(InternalNote $internalNote): RedirectResponse|JsonResponse
    {
        if ($internalNote->agent_id !== ExternalAuth::id()) {
            abort(403);
        }

        $internalNote->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'Nota interna eliminada.',
            ]);
        }

        return back()->with('status', 'Nota interna eliminada.');
    }

    protected function extractMentions(string $content): array
    {
        preg_match_all('/@([\pL0-9_.-]+)/u', $content, $matches);

        return collect($matches[1] ?? [])
            ->map(fn($mention) => '@' . ltrim($mention))
            ->unique()
            ->values()
            ->all();
    }

    protected function storeAttachments(InternalNote $note, array $files): void
    {
        collect($files)
            ->filter(fn($file) => $file instanceof UploadedFile)
            ->each(function (UploadedFile $file) use ($note) {
                $path = $file->store('internal-notes', 'public');

                $note->attachments()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            });
    }

    protected function syncMentions(InternalNote $note, array $mentions, int $agentId, bool $reset = false): void
    {
        if ($reset) {
            $note->mentionsRecords()->delete();
        }

        Collection::make($mentions)->each(function (string $mention) use ($note, $agentId) {
            $note->mentionsRecords()->create([
                'agent_id' => $agentId,
                'mentioned_identifier' => ltrim($mention, '@'),
                'notified_at' => now(),
            ]);
        });
    }
}

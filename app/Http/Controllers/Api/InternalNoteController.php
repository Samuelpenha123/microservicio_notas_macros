<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalNote;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *      schema="InternalNote",
 *      required={"ticket_code", "content", "agent_id"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int64"
 *      ),
 *      @OA\Property(
 *          property="ticket_code",
 *          description="Ticket Code",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="agent_id",
 *          description="Agent ID",
 *          type="integer",
 *          format="int64"
 *      ),
 *       @OA\Property(
 *          property="agent_name",
 *          description="Agent Name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="content",
 *          description="Content",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="is_important",
 *          description="Is Important",
 *          type="boolean"
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
class InternalNoteController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/internal-notes/{ticket}",
     *      operationId="getInternalNotes",
     *      tags={"Internal Notes"},
     *      summary="Get list of internal notes for a ticket",
     *      description="Returns list of internal notes",
     *      @OA\Parameter(
     *          name="ticket",
     *          description="Ticket Code",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          description="Search term",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="author",
     *          description="Filter by author ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="only_important",
     *          description="Filter only important notes",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="with_attachments",
     *          description="Filter notes with attachments",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/InternalNote")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index(Request $request, string $ticketId): JsonResponse
    {
        $notes = InternalNote::query()
            ->forTicketCode($ticketId)
            ->with('attachments')
            ->latest()
            ->search($request->input('search'))
            ->byAuthor($request->integer('author') ?: null)
            ->importantOnly($request->boolean('only_important'))
            ->withAttachmentsOnly($request->boolean('with_attachments'))
            ->get();

        return response()->json([
            'data' => $notes,
        ]);
    }
}



<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Macro;
use App\Models\MacroFavorite;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *      schema="Macro",
 *      required={"name", "content", "scope", "created_by"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int64"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="Name",
 *          type="string"
 *      ),
 *       @OA\Property(
 *          property="content",
 *          description="Content",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="scope",
 *          description="Scope (global/personal)",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="category",
 *          description="Category",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="created_by",
 *          description="Created By Agent ID",
 *          type="integer",
 *          format="int64"
 *      ),
 *       @OA\Property(
 *          property="created_by_name",
 *          description="Created By Name",
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
class MacroController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/macros",
     *      operationId="getMacros",
     *      tags={"Macros"},
     *      summary="Get list of macros",
     *      description="Returns list of macros filtered by criteria",
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
     *          name="category",
     *          description="Filter by category",
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
     *          name="favorites",
     *          description="Filter only favorites",
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
     *                  @OA\Items(ref="#/components/schemas/Macro")
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
    public function index(Request $request): JsonResponse
    {
        $agentId = ExternalAuth::id();

        $macrosQuery = Macro::query()
            ->visibleFor($agentId)
            ->orderBy('name')
            ->search($request->input('search'))
            ->inCategory($request->input('category'))
            ->byAuthor($request->integer('author'));

        if ($request->boolean('favorites')) {
            $favoriteIds = MacroFavorite::query()
                ->where('agent_id', $agentId)
                ->pluck('macro_id');

            $macrosQuery->whereIn('id', $favoriteIds);
        }

        $macros = $macrosQuery->get();

        return response()->json([
            'data' => $macros,
        ]);
    }
}



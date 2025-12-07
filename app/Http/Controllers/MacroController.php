<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMacroRequest;
use App\Http\Requests\UpdateMacroRequest;
use App\Models\Macro;
use App\Models\MacroFavorite;
use App\Support\ExternalAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MacroController extends Controller
{
    /**
     * @OA\Post(
     *      path="/macros",
     *      operationId="storeMacro",
     *      tags={"Macros"},
     *      summary="Store new macro",
     *      description="Creates a new macro",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "content", "scope"},
     *              @OA\Property(property="name", type="string", example="Macro Name"),
     *              @OA\Property(property="content", type="string", example="Macro Content"),
     *              @OA\Property(property="scope", type="string", enum={"personal", "global"}, example="personal"),
     *              @OA\Property(property="category", type="string", example="General")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="data", ref="#/components/schemas/Macro")
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
    public function store(StoreMacroRequest $request): RedirectResponse|JsonResponse
    {
        $macro = Macro::create([
            'name' => $request->input('name'),
            'content' => $request->input('content'),
            'scope' => $request->input('scope', 'personal'),
            'category' => $request->input('category'),
            'created_by' => ExternalAuth::id(),
            'created_by_name' => ExternalAuth::name(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => "Macro {$macro->name} creada.",
                'data' => $macro,
            ]);
        }

        return back()->with('status', "Macro {$macro->name} creada.");
    }

    /**
     * @OA\Put(
     *      path="/macros/{macro}",
     *      operationId="updateMacro",
     *      tags={"Macros"},
     *      summary="Update macro",
     *      description="Updates an existing macro",
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
     *              required={"name", "content", "scope"},
     *              @OA\Property(property="name", type="string", example="Updated Name"),
     *              @OA\Property(property="content", type="string", example="Updated Content"),
     *              @OA\Property(property="scope", type="string", enum={"personal", "global"}, example="personal"),
     *              @OA\Property(property="category", type="string", example="General")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="data", ref="#/components/schemas/Macro")
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
    public function update(UpdateMacroRequest $request, Macro $macro): RedirectResponse|JsonResponse
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

        if ($request->wantsJson()) {
            return response()->json([
                'status' => "Macro {$macro->name} actualizada.",
                'data' => $macro,
            ]);
        }

        return back()->with('status', "Macro {$macro->name} actualizada.");
    }

    /**
     * @OA\Delete(
     *      path="/macros/{macro}",
     *      operationId="deleteMacro",
     *      tags={"Macros"},
     *      summary="Delete macro",
     *      description="Deletes a macro",
     *      @OA\Parameter(
     *          name="macro",
     *          description="Macro ID",
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
    public function destroy(Macro $macro): RedirectResponse|JsonResponse
    {
        $agentId = ExternalAuth::id();

        if ($macro->created_by !== $agentId) {
            abort(403, 'Solo el autor puede eliminar la macro.');
        }

        $macro->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'Macro eliminada.',
            ]);
        }

        return back()->with('status', 'Macro eliminada.');
    }

    /**
     * @OA\Post(
     *      path="/macros/{macro}/favorite",
     *      operationId="toggleFavoriteMacro",
     *      tags={"Macros"},
     *      summary="Toggle favorite macro",
     *      description="Adds or removes macro from favorites",
     *      @OA\Parameter(
     *          name="macro",
     *          description="Macro ID",
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
    public function toggleFavorite(Macro $macro): RedirectResponse|JsonResponse
    {
        $agentId = ExternalAuth::id();

        $favorite = MacroFavorite::query()
            ->where('macro_id', $macro->id)
            ->where('agent_id', $agentId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'status' => 'Macro removida de favoritos.',
                ]);
            }

            return back()->with('status', 'Macro removida de favoritos.');
        }

        MacroFavorite::create([
            'macro_id' => $macro->id,
            'agent_id' => $agentId,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'Macro agregada a favoritos.',
            ]);
        }

        return back()->with('status', 'Macro agregada a favoritos.');
    }
}

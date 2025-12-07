<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Macro;
use App\Models\MacroFavorite;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MacroController extends Controller
{
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

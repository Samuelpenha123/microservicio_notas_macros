<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Macro;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;

class MacroController extends Controller
{
    public function index(): JsonResponse
    {
        $macros = Macro::query()
            ->visibleFor(ExternalAuth::id())
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $macros,
        ]);
    }
}

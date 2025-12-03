<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalNote;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;

class InternalNoteController extends Controller
{
    public function index(string $ticketId): JsonResponse
    {
        $notes = InternalNote::query()
            ->ownedBy(ExternalAuth::id())
            ->forTicketCode($ticketId)
            ->latest()
            ->get();

        return response()->json([
            'data' => $notes,
        ]);
    }
}

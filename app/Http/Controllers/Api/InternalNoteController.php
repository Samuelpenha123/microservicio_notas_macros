<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalNote;
use App\Support\ExternalAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalNoteController extends Controller
{
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

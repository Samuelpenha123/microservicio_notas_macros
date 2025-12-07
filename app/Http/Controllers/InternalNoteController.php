<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternalNoteRequest;
use App\Http\Requests\UpdateInternalNoteRequest;
use App\Models\InternalNote;
use App\Support\ExternalAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class InternalNoteController extends Controller
{
    public function store(StoreInternalNoteRequest $request): RedirectResponse
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

        return back()->with('status', 'Nota interna guardada.');
    }

    public function update(UpdateInternalNoteRequest $request, InternalNote $internalNote): RedirectResponse
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

        return back()->with('status', 'Nota interna actualizada.');
    }

    public function destroy(InternalNote $internalNote): RedirectResponse
    {
        if ($internalNote->agent_id !== ExternalAuth::id()) {
            abort(403);
        }

        $internalNote->delete();

        return back()->with('status', 'Nota interna eliminada.');
    }

    protected function extractMentions(string $content): array
    {
        preg_match_all('/@([\pL0-9_.-]+)/u', $content, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($mention) => '@'.ltrim($mention))
            ->unique()
            ->values()
            ->all();
    }

    protected function storeAttachments(InternalNote $note, array $files): void
    {
        collect($files)
            ->filter(fn ($file) => $file instanceof UploadedFile)
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

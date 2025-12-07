<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalNote extends Model
{
    use HasFactory;

    protected $table = 'internal_notes';

    protected $fillable = [
        'agent_id',
        'agent_name',
        'agent_email',
        'ticket_code',
        'content',
        'is_important',
        'mentions',
    ];

    protected $casts = [
        'is_important' => 'bool',
        'mentions' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function attachments()
    {
        return $this->hasMany(InternalNoteAttachment::class);
    }

    public function mentionsRecords()
    {
        return $this->hasMany(InternalNoteMention::class);
    }

    public function scopeOwnedBy($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeForTicketCode($query, string $ticketCode)
    {
        return $query->where('ticket_code', $ticketCode);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where('content', 'like', "%{$term}%");
    }

    public function scopeByAuthor($query, ?int $agentId)
    {
        if (! $agentId) {
            return $query;
        }

        return $query->where('agent_id', $agentId);
    }

    public function scopeImportantOnly($query, bool $flag)
    {
        if (! $flag) {
            return $query;
        }

        return $query->where('is_important', true);
    }

    public function scopeWithAttachmentsOnly($query, bool $flag)
    {
        if (! $flag) {
            return $query;
        }

        return $query->whereHas('attachments');
    }
}

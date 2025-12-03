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
        'ticket_code',
        'content',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function scopeOwnedBy($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeForTicketCode($query, string $ticketCode)
    {
        return $query->where('ticket_code', $ticketCode);
    }
}

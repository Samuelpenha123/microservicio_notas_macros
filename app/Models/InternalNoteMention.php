<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalNoteMention extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_note_id',
        'agent_id',
        'mentioned_identifier',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(InternalNote::class, 'internal_note_id');
    }
}

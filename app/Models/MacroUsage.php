<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacroUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'macro_id',
        'agent_id',
        'ticket_code',
        'customized',
        'feedback',
        'rendered_content',
    ];

    protected $casts = [
        'customized' => 'bool',
    ];

    public function macro(): BelongsTo
    {
        return $this->belongsTo(Macro::class);
    }
}

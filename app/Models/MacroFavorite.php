<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacroFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'macro_id',
        'agent_id',
    ];

    public function macro(): BelongsTo
    {
        return $this->belongsTo(Macro::class);
    }
}

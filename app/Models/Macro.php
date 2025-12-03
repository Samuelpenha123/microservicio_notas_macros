<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Macro extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'scope',
        'created_by',
    ];

    protected $casts = [
        'scope' => 'string',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleFor($query, int $agentId)
    {
        return $query->where(function ($query) use ($agentId) {
            $query->where('scope', 'global')
                ->orWhere(function ($query) use ($agentId) {
                    $query->where('scope', 'personal')
                        ->where('created_by', $agentId);
                });
        });
    }

    public function scopePersonal($query)
    {
        return $query->where('scope', 'personal');
    }
}

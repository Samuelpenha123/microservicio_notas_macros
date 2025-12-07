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
        'category',
        'created_by',
        'created_by_name',
    ];

    protected $casts = [
        'scope' => 'string',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages()
    {
        return $this->hasMany(MacroUsage::class);
    }

    public function favorites()
    {
        return $this->hasMany(MacroFavorite::class);
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

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%");
        });
    }

    public function scopeInCategory($query, ?string $category)
    {
        if (! $category) {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeByAuthor($query, ?int $authorId)
    {
        if (! $authorId) {
            return $query;
        }

        return $query->where('created_by', $authorId);
    }
}

<?php

namespace App\Models;

use Database\Factories\NavigationMenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'name',
    'placement',
    'channel',
    'nodes',
    'draft_nodes',
    'is_active',
])]
class NavigationMenu extends Model
{
    /** @use HasFactory<NavigationMenuFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nodes' => 'array',
            'draft_nodes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function publishedNodes(): array
    {
        return is_array($this->nodes) ? $this->nodes : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function draftNodes(): array
    {
        return is_array($this->draft_nodes) ? $this->draft_nodes : [];
    }
}

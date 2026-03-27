<?php

namespace App\Models;

use App\Casts\BlockCollectionCast;
use App\PageStatus;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'status', 'published_at', 'blocks'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            $status = $page->status;

            if (
                ($status instanceof PageStatus && $status->isPublished()) ||
                $status === PageStatus::Published->value
            ) {
                $page->published_at ??= now();

                return;
            }

            $page->published_at = null;
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'published_at' => 'immutable_datetime',
            'blocks' => BlockCollectionCast::class,
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published->value);
    }
}

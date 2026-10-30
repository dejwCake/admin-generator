<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $post_id
 * @property int|null $user_id
 * @property string $body
 * @property CarbonInterface|null $deleted_at
 * @property-read Post|null $post
 * @property-read User|null $user
 */
#[Fillable([
    'post_id',
    'user_id',
    'body',
])]
#[Table(timestamps: false)]
#[UseFactory(CommentFactory::class)]
final class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A trashed row can be restored only while every row it belongs to exists and is not trashed.
     */
    public function isRestorable(): bool
    {
        return $this->post()->exists()
            && ($this->user_id === null || $this->user()->exists());
    }

    /**
     * @return array<string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
        ];
    }
}

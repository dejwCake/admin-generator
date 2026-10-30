<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $body
 * @property CarbonInterface|null $deleted_at
 */
#[Fillable([
    'body',
])]
#[Table(timestamps: false)]
#[UseFactory(NoteFactory::class)]
final class Note extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * A trashed row can be restored only while every row it belongs to exists and is not trashed.
     */
    public function isRestorable(): bool
    {
        return true;
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

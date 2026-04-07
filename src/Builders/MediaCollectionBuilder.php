<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Brackets\AdminGenerator\Dtos\Media\MediaCollectionDisk;
use Brackets\AdminGenerator\Dtos\Media\MediaCollectionType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class MediaCollectionBuilder
{
    /** @return Collection<MediaCollection> */
    public function build(array $mediaOptions): Collection
    {
        return (new Collection($mediaOptions))
            ->map(static function (string $media): MediaCollection {
                $parts = explode(':', $media);

                return new MediaCollection(
                    collectionName: $parts[0],
                    type: MediaCollectionType::from($parts[1]),
                    disk: MediaCollectionDisk::from($parts[2]),
                    maxFiles: (int) $parts[3],
                    translationKey: Str::lcfirst($parts[0]),
                    translationValue: Str::headline($parts[0]),
                );
            })->keyBy('collectionName');
    }
}

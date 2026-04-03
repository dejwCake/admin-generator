<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos;

final readonly class MediaCollection
{
    public function __construct(
        public string $collectionName,
        public MediaCollectionType $type,
        public MediaCollectionDisk $disk,
        public int $maxFiles,
    ) {
    }

    public function isImage(): bool
    {
        return $this->type === MediaCollectionType::Image;
    }

    public function isPrivate(): bool
    {
        return $this->disk === MediaCollectionDisk::Private;
    }
}

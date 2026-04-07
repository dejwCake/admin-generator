<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Media;

final readonly class MediaCollection
{
    public function __construct(
        public string $collectionName,
        public MediaCollectionType $type,
        public MediaCollectionDisk $disk,
        public int $maxFiles,
        public string $translationKey,
        public string $translationValue,
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

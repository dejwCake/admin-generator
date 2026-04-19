<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Media;

use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Brackets\AdminGenerator\Dtos\Media\MediaCollectionDisk;
use Brackets\AdminGenerator\Dtos\Media\MediaCollectionType;
use PHPUnit\Framework\TestCase;

final class MediaCollectionTest extends TestCase
{
    public function testIsImageReturnsTrueForImageType(): void
    {
        $mediaCollection = new MediaCollection(
            collectionName: 'gallery',
            type: MediaCollectionType::Image,
            disk: MediaCollectionDisk::Public,
            maxFiles: 10,
            translationKey: 'gallery',
            translationValue: 'Gallery',
        );

        self::assertTrue($mediaCollection->isImage());
    }

    public function testIsImageReturnsFalseForDocumentType(): void
    {
        $mediaCollection = new MediaCollection(
            collectionName: 'documents',
            type: MediaCollectionType::Document,
            disk: MediaCollectionDisk::Public,
            maxFiles: 5,
            translationKey: 'documents',
            translationValue: 'Documents',
        );

        self::assertFalse($mediaCollection->isImage());
    }

    public function testIsPrivateReturnsTrueForPrivateDisk(): void
    {
        $mediaCollection = new MediaCollection(
            collectionName: 'private_files',
            type: MediaCollectionType::Document,
            disk: MediaCollectionDisk::Private,
            maxFiles: 5,
            translationKey: 'private_files',
            translationValue: 'Private Files',
        );

        self::assertTrue($mediaCollection->isPrivate());
    }

    public function testIsPrivateReturnsFalseForPublicDisk(): void
    {
        $mediaCollection = new MediaCollection(
            collectionName: 'gallery',
            type: MediaCollectionType::Image,
            disk: MediaCollectionDisk::Public,
            maxFiles: 10,
            translationKey: 'gallery',
            translationValue: 'Gallery',
        );

        self::assertFalse($mediaCollection->isPrivate());
    }
}

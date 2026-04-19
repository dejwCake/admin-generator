<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders;

use Brackets\AdminGenerator\Builders\MediaCollectionBuilder;
use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Brackets\AdminGenerator\Dtos\Media\MediaCollectionDisk;
use Brackets\AdminGenerator\Dtos\Media\MediaCollectionType;
use PHPUnit\Framework\TestCase;

final class MediaCollectionBuilderTest extends TestCase
{
    private MediaCollectionBuilder $mediaCollectionBuilder;

    protected function setUp(): void
    {
        $this->mediaCollectionBuilder = new MediaCollectionBuilder();
    }

    public function testBuildEmptyArrayReturnsEmptyCollection(): void
    {
        $result = $this->mediaCollectionBuilder->build([]);

        self::assertTrue($result->isEmpty());
    }

    public function testBuildSingleSpecReturnsOneMediaCollection(): void
    {
        $result = $this->mediaCollectionBuilder->build(['gallery:image:public:5']);

        self::assertCount(1, $result);
        self::assertInstanceOf(MediaCollection::class, $result->first());
    }

    public function testBuildKeysCollectionByCollectionName(): void
    {
        $result = $this->mediaCollectionBuilder->build([
            'gallery:image:public:5',
            'documents:document:private:10',
        ]);

        self::assertTrue($result->has('gallery'));
        self::assertTrue($result->has('documents'));
    }

    public function testBuildParsesAllPartsForImageMediaCollection(): void
    {
        $result = $this->mediaCollectionBuilder->build(['gallery:image:public:5']);

        $media = $result->get('gallery');
        self::assertInstanceOf(MediaCollection::class, $media);
        self::assertSame('gallery', $media->collectionName);
        self::assertSame(MediaCollectionType::Image, $media->type);
        self::assertSame(MediaCollectionDisk::Public, $media->disk);
        self::assertSame(5, $media->maxFiles);
    }

    public function testBuildParsesPrivateDocumentMediaCollection(): void
    {
        $result = $this->mediaCollectionBuilder->build(['contracts:document:private:1']);

        $media = $result->get('contracts');
        self::assertInstanceOf(MediaCollection::class, $media);
        self::assertSame(MediaCollectionType::Document, $media->type);
        self::assertSame(MediaCollectionDisk::Private, $media->disk);
        self::assertSame(1, $media->maxFiles);
    }

    public function testBuildSetsTranslationKeyToLcfirstOfName(): void
    {
        $result = $this->mediaCollectionBuilder->build(['MainImage:image:public:1']);

        $media = $result->get('MainImage');
        self::assertInstanceOf(MediaCollection::class, $media);
        self::assertSame('mainImage', $media->translationKey);
    }

    public function testBuildSetsTranslationValueToHeadlineOfName(): void
    {
        $result = $this->mediaCollectionBuilder->build(['main_image:image:public:1']);

        $media = $result->get('main_image');
        self::assertInstanceOf(MediaCollection::class, $media);
        self::assertSame('Main Image', $media->translationValue);
    }

    public function testBuildHandlesMultipleSpecsIndependently(): void
    {
        $result = $this->mediaCollectionBuilder->build([
            'gallery:image:public:5',
            'contracts:document:private:1',
            'avatar:image:private:1',
        ]);

        self::assertCount(3, $result);
        self::assertSame(MediaCollectionType::Image, $result->get('gallery')->type);
        self::assertSame(MediaCollectionType::Document, $result->get('contracts')->type);
        self::assertSame(MediaCollectionDisk::Private, $result->get('avatar')->disk);
    }
}

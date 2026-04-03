<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Media;

enum MediaCollectionDisk: string
{
    case Public = 'public';
    case Private = 'private';
}

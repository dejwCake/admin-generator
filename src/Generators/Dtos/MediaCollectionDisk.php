<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Dtos;

enum MediaCollectionDisk: string
{
    case Public = 'public';
    case Private = 'private';
}

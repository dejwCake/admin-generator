<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Dtos;

enum MediaCollectionType: string
{
    case Image = 'image';
    case Document = 'document';
}

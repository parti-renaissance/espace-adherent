<?php

declare(strict_types=1);

namespace App\Entity\Filesystem;

use MyCLabs\Enum\Enum;

class FileTypeEnum extends Enum
{
    public const FILE = 'file';
    public const DIRECTORY = 'directory';
    public const EXTERNAL_LINK = 'external_link';
}

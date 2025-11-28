<?php

declare(strict_types=1);

namespace App\Image\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ImageUploadedEvent extends Event
{
    public function __construct(public readonly object $entity)
    {
    }
}

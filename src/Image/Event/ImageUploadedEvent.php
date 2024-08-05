<?php

namespace App\Image\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ImageUploadedEvent extends Event
{
    public function __construct(public readonly object $entity)
    {
    }
}

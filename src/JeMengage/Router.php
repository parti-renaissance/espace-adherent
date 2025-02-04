<?php

namespace App\JeMengage;

use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Entity\Jecoute\News;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Router
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function generateLink(object $object): ?string
    {
        if (!$path = $this->getPath($object)) {
            return null;
        }

        return $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).ltrim($path, '/');
    }

    private function getPath(object $object): ?string
    {
        if ($object instanceof Action) {
            return '/actions?uuid='.$object->getUuid();
        }

        if ($object instanceof Event) {
            return '/evenements/'.$object->getSlug();
        }

        if ($object instanceof News) {
            return '/';
        }

        return null;
    }
}

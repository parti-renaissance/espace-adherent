<?php

declare(strict_types=1);

namespace App\JeMengage;

use App\Entity\Action\Action;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\Event\Event;
use App\Entity\Poll\Poll;
use App\Entity\Pronostic\Pronostic;
use App\Entity\TimelineItemPrivateMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Router
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function generateLink(object $object): string
    {
        return $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).ltrim($this->getPath($object), '/');
    }

    private function getPath(object $object): string
    {
        if ($object instanceof Action) {
            return '/actions/'.$object->getUuid();
        }

        if ($object instanceof Event) {
            return '/evenements/'.$object->getSlug();
        }

        if ($object instanceof AdherentMessage) {
            return '/publications/'.$object->getUuid();
        }

        if ($object instanceof Pronostic) {
            return '/prono/'.$object->getUuid();
        }

        if ($object instanceof Poll) {
            return '/sondage/'.$object->getUuid();
        }

        if ($object instanceof TimelineItemPrivateMessage && $object->ctaUrl) {
            return $object->ctaUrl;
        }

        return '/';
    }
}

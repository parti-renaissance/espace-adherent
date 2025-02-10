<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\Repository\Event\EventRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class LiveAlertProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        if (!$events = $this->eventRepository->findWithLiveStream()) {
            return [];
        }

        $alerts = [];
        $now = new \DateTimeImmutable();

        foreach ($events as $event) {
            $url = '/evenements/'.$event->getSlug();

            if ($adherent->getAuthAppVersion() < 5140101 && $event->getBeginAt() < $now) {
                $url = $this->loginLinkHandler->createLoginLink(
                    $adherent,
                    lifetime: 3600,
                    targetPath: parse_url($this->urlGenerator->generate('app_live_event', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL), \PHP_URL_PATH),
                )->getUrl();
            }

            $alerts[] = Alert::createLive($event, $url);
        }

        return $alerts;
    }
}

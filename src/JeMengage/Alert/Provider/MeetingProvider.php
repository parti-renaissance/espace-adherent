<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class MeetingProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly NationalEventRepository $eventRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        if (!$event = $this->eventRepository->findOneActive()) {
            return [];
        }

        $ctaLabel = 'Inscrit';
        $ctaUrl = '';
        $imageUrl = null;
        $eventInscriptionUrl = $this->urlGenerator->generate('app_national_event_by_slug', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($event->ogImage) {
            $imageUrl = $this->urlGenerator->generate('asset_url', ['path' => $event->ogImage->getPath()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if (!$this->eventInscriptionRepository->findOneForAdherent($adherent, $event)) {
            $ctaLabel = 'Je réserve ma place';
            $ctaUrl = $this->loginLinkHandler->createLoginLink(
                $adherent,
                lifetime: 3600,
                targetPath: parse_url($eventInscriptionUrl, \PHP_URL_PATH),
            )->getUrl();
        }

        return [Alert::createMeeting($event, $ctaLabel, $ctaUrl, $imageUrl, $eventInscriptionUrl)];
    }
}

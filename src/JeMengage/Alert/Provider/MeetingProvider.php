<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\JeMengage\Alert\Alert;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class MeetingProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly NationalEventRepository $eventRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        if (!$events = $this->eventRepository->findOneActiveForAlert()) {
            return [];
        }

        $alerts = [];

        foreach ($events as $event) {
            $ctaLabel = 'Inscrit';
            $ctaUrl = '';
            $imageUrl = null;
            $currentUser = $this->getCurrentUser();
            $eventInscriptionUrl = $currentUser
                ? $this->urlGenerator->generate(
                    'app_national_event_by_slug_with_referrer',
                    [
                        'slug' => $event->getSlug(),
                        'referrerCode' => $currentUser->getPublicId(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
                : $this->urlGenerator->generate(
                    'app_national_event_by_slug',
                    [
                        'slug' => $event->getSlug(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

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

            $alerts[] = $alert = Alert::createMeeting($event, $ctaLabel, $ctaUrl, $imageUrl, $eventInscriptionUrl);
            $alert->date = $event->startDate;
        }

        return $alerts;
    }

    private function getCurrentUser(): ?Adherent
    {
        $user = $this->security->getUser();

        return $user instanceof Adherent ? $user : null;
    }
}

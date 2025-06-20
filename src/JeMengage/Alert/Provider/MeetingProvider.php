<?php

namespace App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\JeMengage\Alert\Alert;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class MeetingProvider implements AlertProviderInterface
{
    public function __construct(
        private readonly NationalEventRepository $eventRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
        private readonly UploaderHelperInterface $uploaderHelper,
    ) {
    }

    public function getAlerts(Adherent $adherent): array
    {
        if (!$events = $this->eventRepository->findOneActiveForAlert()) {
            return [];
        }

        $alerts = [];

        foreach ($events as $event) {
            $imageUrl = $data = null;
            $currentUser = $this->getCurrentUser();
            $shareUrl = $currentUser ? $this->generateUrl('app_national_event_by_slug_with_referrer', [
                'slug' => $event->getSlug(),
                'pid' => $currentUser->getPublicId(),
            ]) : $this->generateUrl('app_national_event_by_slug', ['slug' => $event->getSlug()]);

            if ($event->ogImage) {
                $imageUrl = $this->generateUrl('asset_url', ['path' => str_replace('/assets/', '', $this->uploaderHelper->asset($event->ogImage))]);
            }

            if ($inscriptions = $this->eventInscriptionRepository->findAllForAdherentAndEvent($adherent, $event, InscriptionStatusEnum::REFUSED)) {
                if ($event->logoImage) {
                    $imageUrl = $this->generateUrl('asset_url', ['path' => str_replace('/assets/', '', $this->uploaderHelper->asset($event->logoImage))]);
                }

                $ctaLabel = 'Billet bientôt disponible';
                $ctaUrl = null;

                $data = [
                    'first_name' => null,
                    'last_name' => null,
                    'ticket_custom_detail' => null,
                    'ticket_url' => null,
                    'info_url' => null,
                ];

                $ticketSent = current(array_filter($inscriptions, static fn (EventInscription $inscription) => $inscription->ticketSentAt));

                if ($ticketSent instanceof EventInscription && $ticketSent->ticketQRCodeFile) {
                    $ctaLabel = 'Mon billet';
                    $ctaUrl = $this->generateUrl('app_national_event_ticket', ['file' => $ticketSent->ticketQRCodeFile]);

                    $data = [
                        'first_name' => $ticketSent->firstName,
                        'last_name' => $ticketSent->lastName,
                        'ticket_custom_detail' => $ticketSent->ticketCustomDetail,
                        'ticket_url' => $ctaUrl,
                        'info_url' => 'https://parti.re/LP4T',
                    ];
                }
            } else {
                $ctaLabel = 'Je réserve ma place';
                $ctaUrl = $this->loginLinkHandler->createLoginLink(
                    $adherent,
                    lifetime: 3600,
                    targetPath: parse_url(
                        $this->generateUrl('app_national_event_by_slug', ['slug' => $event->getSlug()]),
                        \PHP_URL_PATH
                    ),
                )->getUrl();
            }

            $alerts[] = $alert = Alert::createMeeting($event, $ctaLabel, $ctaUrl, $imageUrl, $shareUrl, $data);
            $alert->date = $event->startDate;
        }

        return $alerts;
    }

    private function getCurrentUser(): ?Adherent
    {
        $user = $this->security->getUser();

        return $user instanceof Adherent ? $user : null;
    }

    private function generateUrl(string $route, array $params): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

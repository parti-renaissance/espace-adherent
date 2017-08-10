<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\CitizenInitiative;
use AppBundle\Events;
use AppBundle\Entity\Adherent;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\EventCancellationMessage;
use AppBundle\Membership\AdherentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenInitiativeMessageNotifier implements EventSubscriberInterface
{
    private $mailjet;
    private $adherentManager;
    private $urlGenerator;

    public function __construct(
        MailjetService $mailjet,
        AdherentManager $adherentManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailjet = $mailjet;
        $this->adherentManager = $adherentManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function onCitizenInitiativeCancelled(CitizenInitiativeCancelledEvent $event): void
    {
        if (!$event->getCitizenInitiative()->isCancelled()) {
            return;
        }

        $subscriptions = $this->adherentManager->findByEvent($event->getCitizenInitiative());

        if (count($subscriptions) > 0) {
            $chunks = array_chunk($subscriptions->toArray(), MailjetService::PAYLOAD_MAXSIZE);

            foreach ($chunks as $chunk) {
                $this->mailjet->sendMessage($this->createCancelMessage(
                    $chunk,
                    $event->getCitizenInitiative(),
                    $event->getAuthor()
                ));
            }
        }
    }

    private function createCancelMessage(array $registered, CitizenInitiative $initiative, Adherent $host): EventCancellationMessage
    {
        return EventCancellationMessage::create(
            $registered,
            $host,
            $initiative,
            $this->generateUrl('app_search_events'),
            function (Adherent $adherent) {
                return EventCancellationMessage::getRecipientVars($adherent->getFirstName());
            }
        );
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CITIZEN_INITIATIVE_CANCELLED => ['onCitizenInitiativeCancelled', -128],
        ];
    }
}

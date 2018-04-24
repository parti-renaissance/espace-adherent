<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Entity\Adherent;
use AppBundle\RepublicanSilence\AdherentZone\AdherentZoneRetrieverInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckRepublicanSilenceListener implements EventSubscriberInterface
{
    private const ROUTES = [
        // Referent Space
        'app_referent_users' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_REFERENT,
        'app_referent_users_message' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_REFERENT,
        'app_referent_events_create' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_REFERENT,

        // Committee
        'app_committee_show' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_committee_contact_members' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_committee_manager_add_event' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,

        // Citizen Project
        'app_citizen_project_show_comments' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_CITIZEN_PROJECT_ADMINISTRATOR,
        'app_citizen_project_contact_actors' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_CITIZEN_PROJECT_ADMINISTRATOR,
        'app_citizen_action_manager_create' => AdherentZoneRetrieverInterface::ADHERENT_TYPE_CITIZEN_PROJECT_ADMINISTRATOR,
    ];

    private $tokenStorage;
    private $republicanSilenceManager;
    private $templateEngine;

    public function __construct(TokenStorageInterface $tokenStorage, Manager $manager, EngineInterface $engine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->republicanSilenceManager = $manager;
        $this->templateEngine = $engine;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    public function onRequest(GetResponseEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        /** @var Adherent $user */
        if (!($token = $this->tokenStorage->getToken()) || !($user = $token->getUser()) || !$this->supportUser($user)) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if (!$this->supportRoute($route)) {
            return;
        }

        $zoneRetriever = ZoneRetrieverFactory::create(self::ROUTES[$route]);

        if (!$userZones = $zoneRetriever->getAdherentZone($user, $event->getRequest())) {
            return;
        }

        if ($this->republicanSilenceManager->hasStartedSilence($userZones)) {
            $event->setResponse($this->templateEngine->renderResponse('republican_silence/landing.html.twig'));
        }
    }

    private function supportRoute(string $route): bool
    {
        return array_key_exists($route, self::ROUTES);
    }

    private function supportUser($user): bool
    {
        return $user instanceof Adherent
            && (
                $user->isHost()
                || $user->isSupervisor()
                || $user->isReferent()
                || $user->isCitizenProjectAdministrator()
            );
    }
}

<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckRepublicanSilenceListener implements EventSubscriberInterface
{
    private const ROUTES = [
        'app_referent_users' => AdherentZoneRetriever::ADHERENT_TYPE_REFERENT,
        'app_referent_users_message' => AdherentZoneRetriever::ADHERENT_TYPE_REFERENT,
        'app_referent_events_create' => AdherentZoneRetriever::ADHERENT_TYPE_REFERENT,
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

        $route = $event->getRequest()->attributes->get('_route');
        if (!$this->supportRoute($route)) {
            return;
        }

        /** @var Adherent $user */
        $user = $this->tokenStorage->getToken()->getUser();
        dump($user->getMemberships());
        if (!$userZones = AdherentZoneRetriever::getAdherentZone($user, self::ROUTES[$route])) {
            return;
        }

        if ($this->republicanSilenceManager->hasStartedSilence($userZones)) {
            //$event->setResponse($this->templateEngine->renderResponse('republican_silence/landing.html.twig'));
            dump('BLOCK !!!!!!');
        }
    }

    private function supportRoute(string $route): bool
    {
        return array_key_exists($route, self::ROUTES);
    }
}

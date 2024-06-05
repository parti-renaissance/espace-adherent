<?php

namespace App\RepublicanSilence;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\RepublicanSilence\ZoneExtractor\ZoneExtractorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class CheckRepublicanSilenceListener implements EventSubscriberInterface
{
    private const ROUTES = [
        // Referent Space
        'app_referent_managed_users_list' => ZoneExtractorInterface::ADHERENT_TYPE_REFERENT,
        'app_referent_event_manager_create' => ZoneExtractorInterface::ADHERENT_TYPE_REFERENT,
        'app_message_send' => ZoneExtractorInterface::ADHERENT_TYPE_REFERENT,
        'app_message_referent_*' => ZoneExtractorInterface::ADHERENT_TYPE_REFERENT,

        // Committee
        'app_committee_show' => ZoneExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_committee_contact_members' => ZoneExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_committee_manager_add_event' => ZoneExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_message_committee_*' => ZoneExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,

        // Deputy Space
        'app_message_deputy_*' => ZoneExtractorInterface::ADHERENT_TYPE_DEPUTY,
        'app_deputy_event_manager_create' => ZoneExtractorInterface::ADHERENT_TYPE_DEPUTY,

        // Senator Space
        'app_message_senator_*' => ZoneExtractorInterface::ADHERENT_TYPE_SENATOR,
        'app_senator_event_manager_create' => ZoneExtractorInterface::ADHERENT_TYPE_SENATOR,

        // Candidate Space
        'app_candidate_*' => ZoneExtractorInterface::NONE,
        'app_jecoute_candidate_*' => ZoneExtractorInterface::NONE,
        'app_jecoute_news_candidate_*' => ZoneExtractorInterface::NONE,

        // Procuration Space
        'app_procuration_manager_*' => ZoneExtractorInterface::ADHERENT_TYPE_PROCURATION_MANAGER,

        // All message actions
        'app_message_*' => ZoneExtractorInterface::NONE,

        // All API endpoints
        '/api/v3/' => ZoneExtractorInterface::NONE,
    ];

    private TokenStorageInterface $tokenStorage;
    private RepublicanSilenceManager $republicanSilenceManager;
    private Environment $templateEngine;
    private ZoneExtractorFactory $zoneExtractorFactory;
    private SessionInterface $session;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RepublicanSilenceManager $manager,
        Environment $engine,
        ZoneExtractorFactory $zoneExtractorFactory,
        SessionInterface $session
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->republicanSilenceManager = $manager;
        $this->templateEngine = $engine;
        $this->zoneExtractorFactory = $zoneExtractorFactory;
        $this->session = $session;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        /** @var Adherent $user */
        if (!($token = $this->tokenStorage->getToken()) || !($user = $token->getUser()) || !$this->supportUser($user)) {
            return;
        }

        if ($delegatedAccess = $user->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $user = $delegatedAccess->getDelegator();
        }

        $route = ($request = $event->getRequest())->attributes->get('_route');

        if (null === $type = $this->getRouteType($route, $request->getPathInfo())) {
            return;
        }

        if (ZoneExtractorInterface::NONE === $type) {
            if (!\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_DELETE])) {
                return;
            }

            if ($this->republicanSilenceManager->hasStartedSilence()) {
                $this->setResponse($event);
            }

            return;
        }

        $zoneExtractor = $this->zoneExtractorFactory->create($type);

        if (!$zones = $zoneExtractor->extractZones($user, $this->getSlug($event->getRequest(), $type))) {
            return;
        }

        if ($this->republicanSilenceManager->hasStartedSilence($zones)) {
            $this->setResponse($event);
        }
    }

    private function getRouteType(string $currentRoute, string $path): ?int
    {
        foreach (self::ROUTES as $routeNameOrPath => $type) {
            if (str_starts_with($routeNameOrPath, '/')) {
                if (str_starts_with($path, $routeNameOrPath)) {
                    return $type;
                }
            } else {
                if ($currentRoute === $routeNameOrPath) {
                    return $type;
                }

                if (str_ends_with($routeNameOrPath, '*') && str_contains($currentRoute, rtrim($routeNameOrPath, '*'))) {
                    return $type;
                }
            }
        }

        return null;
    }

    private function supportUser($user): bool
    {
        return $user instanceof Adherent;
    }

    private function getSlug(Request $request, string $type): ?string
    {
        switch ($type) {
            case ZoneExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR:
                return $request->attributes->get('slug', $request->attributes->get('committee_slug'));
        }

        return null;
    }

    private function setResponse(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (str_starts_with($request->getPathInfo(), '/api/')) {
            $event->setResponse(new JsonResponse(['message' => 'En raison du silence républicain, cette action est momentanément désactivée.'], Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS));

            return;
        }

        $event->setResponse(new Response($this->templateEngine->render('republican_silence/landing.html.twig')));
    }
}

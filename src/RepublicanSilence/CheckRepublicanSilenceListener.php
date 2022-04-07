<?php

namespace App\RepublicanSilence;

use App\Entity\Adherent;
use App\RepublicanSilence\TagExtractor\ReferentTagExtractorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class CheckRepublicanSilenceListener implements EventSubscriberInterface
{
    private const ROUTES = [
        // Referent Space
        'app_referent_managed_users_list' => ReferentTagExtractorInterface::ADHERENT_TYPE_REFERENT,
        'app_referent_users_message' => ReferentTagExtractorInterface::ADHERENT_TYPE_REFERENT,
        'app_referent_event_manager_create' => ReferentTagExtractorInterface::ADHERENT_TYPE_REFERENT,
        'app_message_send' => ReferentTagExtractorInterface::ADHERENT_TYPE_REFERENT,
        'app_message_referent_*' => ReferentTagExtractorInterface::ADHERENT_TYPE_REFERENT,

        // Committee
        'app_committee_show' => ReferentTagExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_committee_contact_members' => ReferentTagExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_committee_manager_add_event' => ReferentTagExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,
        'app_message_committee_*' => ReferentTagExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR,

        // Deputy Space
        'app_message_deputy_*' => ReferentTagExtractorInterface::ADHERENT_TYPE_DEPUTY,
        'app_deputy_event_manager_create' => ReferentTagExtractorInterface::ADHERENT_TYPE_DEPUTY,

        // Municipal Space
        'app_municipal_chief_event_manager_create' => ReferentTagExtractorInterface::ADHERENT_TYPE_MUNICIPAL_CHIEF,
        'app_message_municipal_chief_*' => ReferentTagExtractorInterface::ADHERENT_TYPE_MUNICIPAL_CHIEF,

        // Senator Space
        'app_message_senator_*' => ReferentTagExtractorInterface::ADHERENT_TYPE_SENATOR,
        'app_senator_event_manager_create' => ReferentTagExtractorInterface::ADHERENT_TYPE_SENATOR,

        // Candidate Space
        'app_candidate_*' => ReferentTagExtractorInterface::NONE,
        'app_jecoute_candidate_*' => ReferentTagExtractorInterface::NONE,
        'app_jecoute_news_candidate_*' => ReferentTagExtractorInterface::NONE,

        // Procuration Space
        'app_procuration_manager_*' => ReferentTagExtractorInterface::ADHERENT_TYPE_PROCURATION_MANAGER,

        // LRE Space
        'app_lre_*' => ReferentTagExtractorInterface::NONE,

        // All message actions
        'app_message_*' => ReferentTagExtractorInterface::NONE,
    ];

    private $tokenStorage;
    private $republicanSilenceManager;
    private $templateEngine;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RepublicanSilenceManager $manager,
        Environment $engine
    ) {
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

    public function onRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        /** @var Adherent $user */
        if (!($token = $this->tokenStorage->getToken()) || !($user = $token->getUser()) || !$this->supportUser($user)) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if (null === $type = $this->getRouteType($route)) {
            return;
        }

        if (ReferentTagExtractorInterface::NONE === $type) {
            if ($this->republicanSilenceManager->hasStartedSilence()) {
                $this->setResponse($event);
            }

            return;
        }

        $tagExtractor = ReferentTagExtractorFactory::create($type);

        if (!$tags = $tagExtractor->extractTags($user, $this->getSlug($event->getRequest(), $type))) {
            return;
        }

        if ($this->republicanSilenceManager->hasStartedSilence($tags)) {
            $this->setResponse($event);
        }
    }

    private function getRouteType(string $currentRoute): ?int
    {
        foreach (self::ROUTES as $routeName => $type) {
            if ($currentRoute === $routeName) {
                return $type;
            }

            if ('*' === substr($routeName, -1) && false !== strpos($currentRoute, rtrim($routeName, '*'))) {
                return $type;
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
            case ReferentTagExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR:
                return $request->attributes->get('slug', $request->attributes->get('committee_slug'));
        }

        return null;
    }

    private function setResponse(RequestEvent $event): void
    {
        $event->setResponse(new Response($this->templateEngine->render('republican_silence/landing.html.twig')));
    }
}

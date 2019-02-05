<?php

namespace AppBundle\Security\EventListener;

use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LastVisitedPageOnGetListener implements EventSubscriberInterface
{
    use TargetPathTrait;

    private const FIREWALL_NAME = 'main';
    private const IGNORED_ROUTES = [
        'app_user_login',
        'logout',
        'asset_url',
        'app_membership_activate',
    ];

    private $security;
    private $firewallMap;
    private $apiPathPrefix;

    public function __construct(string $apiPathPrefix, Security $security, FirewallMap $firewallMap)
    {
        $this->apiPathPrefix = $apiPathPrefix;
        $this->security = $security;
        $this->firewallMap = $firewallMap;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -1],
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $token = $this->security->getToken();

        // Stop if not MasterRequest or Response != 200
        if (
            !$event->isMasterRequest()
            || Response::HTTP_OK !== $event->getResponse()->getStatusCode()
            || 0 !== strpos($event->getResponse()->headers->get('content-type'), 'text/html')
        ) {
            return;
        }

        // Stop if no Session or it's not an anonymous
        if (!$request->hasSession() || !$token instanceof AnonymousToken) {
            return;
        }

        // Stop if not GET or XHR request
        if (Request::METHOD_GET !== $request->getMethod() || $request->isXmlHttpRequest()) {
            return;
        }

        // Stop for API routes or for ignored routes
        if (
            0 === mb_strpos($request->getRequestUri(), $this->apiPathPrefix)
            || \in_array($request->attributes->get('_route'), self::IGNORED_ROUTES)
        ) {
            return;
        }

        if (self::FIREWALL_NAME === $this->getFirewallName($request)) {
            $this->saveTargetPath($request->getSession(), self::FIREWALL_NAME, $request->getUri());
        }
    }

    private function getFirewallName(Request $request): ?string
    {
        if ($config = $this->firewallMap->getFirewallConfig($request)) {
            return $config->getName();
        }

        return null;
    }
}

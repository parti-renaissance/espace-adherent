<?php

declare(strict_types=1);

namespace App\Security\Listener;

use App\Entity\Administrator;
use App\Repository\OAuth\ClientRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener implements EventSubscriberInterface
{
    public function __construct(private readonly ClientRepository $clientRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        if ($event->getToken()?->getUser() instanceof Administrator) {
            return;
        }

        $request = $event->getRequest();
        $client = $this->clientRepository->getVoxClient();
        $redirectUri = $request->query->get('redirect_uri');

        if (!$redirectUri || !\in_array($redirectUri, $client->getRedirectUris())) {
            $redirectUri = current($client->getRedirectUris());
        }

        $event->setResponse(new RedirectResponse($redirectUri, Response::HTTP_SEE_OTHER));
    }
}

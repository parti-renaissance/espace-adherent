<?php

namespace AppBundle\Membership\EventListener;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentChangeEmailTokenRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChangeEmailFlashMessageSubscriber implements EventSubscriberInterface
{
    private const MESSAGE = 'adherent.change_email.email_sent';

    private $tokenStorage;
    private $repository;
    private $session;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AdherentChangeEmailTokenRepository $repository,
        Session $session
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $repository;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER => 'setChangeEmailFlashMessage'];
    }

    public function setChangeEmailFlashMessage(FilterControllerEvent $event): void
    {
        if (!$this->support($event)) {
            return;
        }

        if ($this->repository->findLastUnusedByAdherent($this->tokenStorage->getToken()->getUser())) {
            $this->session->getFlashBag()->add('info', self::MESSAGE);
        }
    }

    /**
     * Support returns true only for:
     *  - Master request
     *  - Not XmlHttpRequest
     *  - Request with Accept: text/html header
     *  - Request with connected user
     *  - Route should not be `user_validate_new_email`
     *  - FlashBag should not have already the same message
     */
    private function support(FilterControllerEvent $event): bool
    {
        if (!$event->isMasterRequest() || $event->getRequest()->isXmlHttpRequest()) {
            return false;
        }

        if (!in_array('text/html', $event->getRequest()->getAcceptableContentTypes())) {
            return false;
        }

        if (!($token = $this->tokenStorage->getToken()) || !$token->getUser() instanceof Adherent) {
            return false;
        }

        if ('user_validate_new_email' === $event->getRequest()->attributes->get('_route')) {
            return false;
        }

        if (in_array(self::MESSAGE, $this->session->getFlashBag()->peek('info'), true)) {
            return false;
        }

        return true;
    }
}

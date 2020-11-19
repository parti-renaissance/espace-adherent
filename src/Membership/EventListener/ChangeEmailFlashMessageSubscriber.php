<?php

namespace App\Membership\EventListener;

use App\Entity\Adherent;
use App\Repository\AdherentChangeEmailTokenRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ChangeEmailFlashMessageSubscriber implements EventSubscriberInterface
{
    private const MESSAGE = 'adherent.change_email.email_sent';

    private $tokenStorage;
    private $repository;
    private $session;
    private $translator;
    private $message;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AdherentChangeEmailTokenRepository $repository,
        Session $session,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $repository;
        $this->session = $session;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'setChangeEmailFlashMessage',
            KernelEvents::RESPONSE => 'removeMessageOnRedirection',
        ];
    }

    public function setChangeEmailFlashMessage(FilterControllerEvent $event): void
    {
        if (!$this->support($event)) {
            return;
        }

        if ($token = $this->repository->findLastUnusedByAdherent($this->tokenStorage->getToken()->getUser())) {
            $this->session->getFlashBag()->add('info', $this->message = $this->translator->trans(
                self::MESSAGE,
                ['{{ email }}' => $token->getEmail()]
            ));
        }
    }

    public function removeMessageOnRedirection(FilterResponseEvent $event): void
    {
        if ($event->getResponse()->isRedirection()) {
            $messages = $this->session->getFlashBag()->peek('info');
            $this->session->getFlashBag()->set('info', array_filter($messages, function (string $message) {
                return $this->message !== $message;
            }));
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

        if (!\in_array('text/html', $event->getRequest()->getAcceptableContentTypes())) {
            return false;
        }

        if (!($token = $this->tokenStorage->getToken()) || !$token->getUser() instanceof Adherent) {
            return false;
        }

        if ('user_validate_new_email' === $event->getRequest()->attributes->get('_route')) {
            return false;
        }

        if (\in_array($this->message, $this->session->getFlashBag()->peek('info'), true)) {
            return false;
        }

        return true;
    }
}

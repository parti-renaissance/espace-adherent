<?php

namespace App\Membership\EventListener;

use App\Entity\Adherent;
use App\Repository\AdherentChangeEmailTokenRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        SessionInterface $session,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $repository;
        $this->session = $session;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'setChangeEmailFlashMessage',
            KernelEvents::RESPONSE => 'removeMessageOnRedirection',
        ];
    }

    public function setChangeEmailFlashMessage(ControllerEvent $event): void
    {
        if (!$this->support($event)) {
            return;
        }

        if ($token = $this->repository->findLastUnusedByAdherent($this->tokenStorage->getToken()?->getUser())) {
            $this->session->getFlashBag()->add('info', $this->message = $this->translator->trans(
                self::MESSAGE,
                ['email' => $token->getEmail()]
            ));
        }
    }

    public function removeMessageOnRedirection(ResponseEvent $event): void
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
    private function support(ControllerEvent $event): bool
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

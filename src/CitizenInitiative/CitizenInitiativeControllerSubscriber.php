<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Controller\EnMarche\CitizenInitiativeController;
use AppBundle\Controller\EnMarche\CitizenInitiativeManagerContoller;
use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class is used for the period of testing IC only in 4 departments.
 */
class CitizenInitiativeControllerSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;
    private $tokenStorage;

    public function __construct(UrlGeneratorInterface $urlGenerator, TokenStorageInterface $tokenStorage)
    {
        $this->urlGenerator = $urlGenerator;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if (($controller[0] instanceof CitizenInitiativeController || $controller[0] instanceof CitizenInitiativeManagerContoller)
            && 'showIfNotAuthorizedDepartementAction' !== $controller[1]) {
            $event->getRequest()->attributes->set('is_ci_controller', true);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->get('is_ci_controller', false)) {
            return;
        }

        $connectedUser = $this->tokenStorage->getToken()->getUser();
        if (!$connectedUser instanceof Adherent) {
            return;
        }

        if (!$this->isInAuthorizedDepartment($connectedUser)) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_citizen_initiative_not_available')));
        }
    }

    public function isInAuthorizedDepartment(Adherent $user)
    {
        if ('FR' !== $user->getCountry()) {
            return false;
        }

        $postalCode = $user->getPostalCode();
        if (in_array($postalCode, ['75018'], true)) {
            return true;
        }

        return in_array(substr($postalCode, 0, 2), ['16', '80', '81'], true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}

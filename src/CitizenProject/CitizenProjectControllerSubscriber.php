<?php

namespace AppBundle\CitizenProject;

use AppBundle\Controller\EnMarche\AdherentController;
use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This class is used for the period of testing CP only in 30 departments.
 */
class CitizenProjectControllerSubscriber implements EventSubscriberInterface
{
    public const UNAUTHORIZED_DEPARTMENT_CODES = ['971', '972', '973', '974', '975', '976', '984', '986', '987', '988'];

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

        if (($controller[0] instanceof AdherentController && 'createCitizenProjectAction' === $controller[1])) {
            $event->getRequest()->attributes->set('is_cp_controller', true);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->get('is_cp_controller', false)) {
            return;
        }

        $connectedUser = $this->tokenStorage->getToken()->getUser();
        if (!$connectedUser instanceof Adherent) {
            return;
        }

        if (!$this->isInAuthorizedDepartment($connectedUser)) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_citizen_project_not_available')));
        }
    }

    public function isInAuthorizedDepartment(Adherent $user)
    {
        if ('FR' !== $user->getCountry()) {
            return false;
        }

        $postalCode = $user->getPostalCode();

        return !in_array(substr($postalCode, 0, 3), self::UNAUTHORIZED_DEPARTMENT_CODES, true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}

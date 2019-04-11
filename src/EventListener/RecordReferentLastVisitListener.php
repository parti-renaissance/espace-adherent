<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\ReferentSpaceAccessInformation;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class RecordReferentLastVisitListener implements EventSubscriberInterface
{
    private $security;
    private $authorizationChecker;
    private $manager;
    private $repository;

    public function __construct(
        Security $security,
        AuthorizationCheckerInterface $authorizationChecker,
        ObjectManager $manager
    ) {
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
        $this->manager = $manager;
        $this->repository = $this->manager->getRepository(ReferentSpaceAccessInformation::class);
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

        // Stop if not GET or not MasterRequest
        if (Request::METHOD_GET !== $request->getMethod() || !$event->isMasterRequest()) {
            return;
        }

        // Stop for non referent space routes
        if (0 !== mb_strpos($request->attributes->get('_route'), 'app_referent')) {
            return;
        }

        // Stop if no logged in user
        if (!$referent = $this->security->getUser()) {
            return;
        }

        // Stop if Response != 200
        if (Response::HTTP_OK !== $event->getResponse()->getStatusCode()) {
            return;
        }

        $referentSpaceAccessInformation = $this->repository->findByAdherent($referent);

        if ($referentSpaceAccessInformation) {
            $referentSpaceAccessInformation->update();
        } else {
            $referentSpaceAccessInformation = new ReferentSpaceAccessInformation($referent, new \DateTimeImmutable(), new \DateTimeImmutable());
            $this->manager->persist($referentSpaceAccessInformation);
        }

        $this->manager->flush();
    }
}

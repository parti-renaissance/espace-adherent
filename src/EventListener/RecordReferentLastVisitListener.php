<?php

namespace App\EventListener;

use App\Entity\Adherent;
use App\Entity\ReferentSpaceAccessInformation;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class RecordReferentLastVisitListener implements EventSubscriberInterface
{
    private $security;
    private $manager;
    private $repository;

    public function __construct(Security $security, ObjectManager $manager)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->repository = $this->manager->getRepository(ReferentSpaceAccessInformation::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -1],
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        // Stop if not GET or not MasterRequest
        if (Request::METHOD_GET !== $request->getMethod() || !$event->isMainRequest()) {
            return;
        }

        // Stop for non referent space routes
        if (0 !== mb_strpos($request->attributes->get('_route'), 'app_referent')) {
            return;
        }

        // Stop if Response != 200
        if (Response::HTTP_OK !== $event->getResponse()->getStatusCode()) {
            return;
        }

        $referent = $this->security->getUser();

        // Stop if no logged in user
        if (!$referent instanceof Adherent) {
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

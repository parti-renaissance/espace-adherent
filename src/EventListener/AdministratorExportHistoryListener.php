<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Administrator;
use AppBundle\Entity\Reporting\AdministratorExportHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class AdministratorExportHistoryListener implements EventSubscriberInterface
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $administrator = $this->security->getUser();

        if (!$administrator instanceof Administrator) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->get('_route');

        if (!preg_match('/^admin_(.)+_export$/', $routeName)) {
            return;
        }

        $history = new AdministratorExportHistory($administrator, $routeName, $request->query->all());

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}

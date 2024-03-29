<?php

namespace App\EventListener;

use App\Entity\Administrator;
use App\Entity\Reporting\AdministratorExportHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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

    public function onKernelResponse(ResponseEvent $event)
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

        $history = new AdministratorExportHistory($administrator, $routeName, $request->query->all(), new \DateTime());

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}

<?php

namespace App\Adherent\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Reporting\DeclaredMandateHistory;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class DeclaredMandateChangeListener implements EventSubscriberInterface
{
    private array $oldDeclaredMandates = [];

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_BEFORE_UPDATE => 'beforeUpdate',
            UserEvents::USER_UPDATED => 'afterUpdate',
        ];
    }

    public function beforeUpdate(UserEvent $event): void
    {
        $this->oldDeclaredMandates = $event->getUser()->getMandates();
    }

    public function afterUpdate(UserEvent $event): void
    {
        $adherent = $event->getUser();
        $newDeclaredMandates = $adherent->getMandates();

        $addedMandates = array_diff($newDeclaredMandates, $this->oldDeclaredMandates);
        $removedMandates = array_diff($this->oldDeclaredMandates, $newDeclaredMandates);

        if ($addedMandates || $removedMandates) {
            $this->entityManager->persist($this->createHistory($adherent, $addedMandates, $removedMandates));

            $this->entityManager->flush();
        }
    }

    private function createHistory(Adherent $adherent, array $addedMandates, array $removedMandates): DeclaredMandateHistory
    {
        $history = new DeclaredMandateHistory($adherent, $addedMandates, $removedMandates);

        $user = $this->security->getUser();
        if ($user instanceof Administrator) {
            $history->setAdministrator($user);
        }

        return $history;
    }
}

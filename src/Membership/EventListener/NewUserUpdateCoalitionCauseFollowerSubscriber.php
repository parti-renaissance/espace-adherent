<?php

namespace App\Membership\EventListener;

use App\Entity\Coalition\CauseFollower;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\Repository\Coalition\CauseFollowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewUserUpdateCoalitionCauseFollowerSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $causeFollowerRepository;

    public function __construct(EntityManagerInterface $entityManager, CauseFollowerRepository $causeFollowerRepository)
    {
        $this->entityManager = $entityManager;
        $this->causeFollowerRepository = $causeFollowerRepository;
    }

    public function updateFollower(UserEvent $event): void
    {
        $adherent = $event->getUser();

        foreach ($this->causeFollowerRepository->findBy(['emailAddress' => $adherent->getEmailAddress()]) as $causeFollower) {
            /** @var CauseFollower $causeFollower */
            $causeFollower->setAdherent($adherent);
            $causeFollower->setEmailAddress(null);
        }

        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_CREATED => 'updateFollower',
        ];
    }
}

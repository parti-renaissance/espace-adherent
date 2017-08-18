<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\ActivitySubscription;
use AppBundle\Entity\Adherent;
use AppBundle\Repository\ActivitySubscriptionRepository;
use Doctrine\Common\Persistence\ObjectManager;

class ActivitySubscriptionManager
{
    private $repository;
    private $manager;

    public function __construct(
        ActivitySubscriptionRepository $repository,
        ObjectManager $manager
    ) {
        $this->repository = $repository;
        $this->manager = $manager;
    }

    public function subscribeToAdherentActivity(Adherent $following, Adherent $followed): void
    {
        $activitySubscription = $this->repository->findSubscription($following, $followed);

        if (!$activitySubscription) {
            $activitySubscription = new ActivitySubscription($following, $followed);
        } elseif ($activitySubscription->isSubscribed()) {
            $activitySubscription->setUnsubscribedAt(new \DateTime());
        } else {
            $activitySubscription->setSubscribedAt(new \DateTime());
        }

        if (!$activitySubscription->getId()) {
            $this->manager->persist($activitySubscription);
        }

        $this->manager->flush();
    }
}

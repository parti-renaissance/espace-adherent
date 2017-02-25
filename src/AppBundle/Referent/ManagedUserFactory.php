<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManager;

class ManagedUserFactory
{
    /**
     * @var AdherentRepository
     */
    private $adherentsRepository;

    /**
     * @var NewsletterSubscriptionRepository
     */
    private $newsletterSubscriptionsRepository;

    public function __construct(EntityManager $manager)
    {
        $this->adherentsRepository = $manager->getRepository(Adherent::class);
        $this->newsletterSubscriptionsRepository = $manager->getRepository(NewsletterSubscription::class);
    }

    /**
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function createManagedUsersCollectionFor(Adherent $referent): array
    {
        return $this->aggregate(
            $this->newsletterSubscriptionsRepository->findAllManagedBy($referent),
            $this->adherentsRepository->findAllManagedBy($referent)
        );
    }

    /**
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function createManagedSubscribersCollectionFor(Adherent $referent): array
    {
        return $this->aggregate(
            $this->newsletterSubscriptionsRepository->findAllManagedBy($referent),
            []
        );
    }

    /**
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function createManagedAdherentsCollectionFor(Adherent $referent): array
    {
        return $this->aggregate(
            [],
            $this->adherentsRepository->findAllManagedBy($referent)
        );
    }

    /**
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function createManagedNonFollowersCollectionFor(Adherent $referent): array
    {
        return $this->aggregate(
            [],
            $this->adherentsRepository->findNonFollowersManagedBy($referent)
        );
    }

    /**
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function createManagedFollowersCollectionFor(Adherent $referent): array
    {
        return $this->aggregate(
            [],
            $this->adherentsRepository->findFollowersManagedBy($referent)
        );
    }

    /**
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function createManagedHostsCollectionFor(Adherent $referent): array
    {
        return $this->aggregate(
            [],
            $this->adherentsRepository->findHostsManagedBy($referent)
        );
    }

    /**
     * Aggregate adherents and newsletter subscribers of the given referent managed area
     * into a single collection.
     *
     * @param NewsletterSubscription[] $newsletterSubscriptions
     * @param Adherent[]               $adherents
     *
     * @return array
     */
    private function aggregate(array $newsletterSubscriptions, array $adherents)
    {
        $managedUsers = [];

        foreach ($newsletterSubscriptions as $subscription) {
            $managedUsers[$subscription->getEmail()] = ManagedUser::createFromNewsletterSubscription($subscription);
        }

        foreach ($adherents as $adherent) {
            $managedUsers[$adherent->getEmailAddress()] = ManagedUser::createFromAdherent($adherent);
        }

        usort($managedUsers, function (ManagedUser $a, ManagedUser $b) {
            return strnatcmp(
                $a->getPostalCode().'-'.strtolower($a->getFirstName()),
                $b->getPostalCode().'-'.strtolower($b->getFirstName())
            );
        });

        return $managedUsers;
    }
}

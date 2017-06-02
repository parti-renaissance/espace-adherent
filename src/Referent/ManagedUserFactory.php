<?php

namespace AppBundle\Referent;

use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManager;

class ManagedUserFactory
{
    private $adherentsRepository;
    private $newsletterSubscriptionsRepository;
    private $committeesRepository;

    public function __construct(EntityManager $manager)
    {
        $this->adherentsRepository = $manager->getRepository(Adherent::class);
        $this->newsletterSubscriptionsRepository = $manager->getRepository(NewsletterSubscription::class);
        $this->committeesRepository = $manager->getRepository(Committee::class);
    }

    /**
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function createManagedUsersListIndexedByTypeAndId(Adherent $referent): array
    {
        $registry = [];
        foreach ($this->createManagedUsersCollectionFor($referent) as $user) {
            if ($user->hasReferentsEmailsSubscription()) {
                $registry[$user->getType()][$user->getId()] = $user;
            }
        }

        return $registry;
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
        $committeesRepository = $this->committeesRepository;

        $hosts = $this->adherentsRepository->findHostsManagedBy($referent);
        $approvedHosts = array_filter($hosts, function (Adherent $adherent) use ($committeesRepository) {
            $memberships = $adherent->getMemberships();
            $memberships = is_array($memberships) ? $memberships : $memberships->toArray();
            $memberships = new CommitteeMembershipCollection($memberships);

            $uuids = $memberships->getCommitteeHostMemberships()->getCommitteeUuids();

            return count($committeesRepository->findCommittees($uuids, CommitteeRepository::ONLY_APPROVED)) > 0;
        });

        return $this->aggregate(
            [],
            $approvedHosts
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
    private function aggregate($newsletterSubscriptions, $adherents)
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
                $b->getCreatedAt()->format('Y-m-d H:i'),
                $a->getCreatedAt()->format('Y-m-d H:i')
            );
        });

        return $managedUsers;
    }
}

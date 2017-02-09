<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManager;

class DataGridFactory
{
    /** @var AdherentRepository */
    private $adherentsRepository;

    /** @var NewsletterSubscriptionRepository */
    private $newsletterSubscriptionsRepository;

    /** @var CommitteeRepository */
    private $committeesRepository;

    public function __construct(EntityManager $manager)
    {
        $this->adherentsRepository = $manager->getRepository(Adherent::class);
        $this->newsletterSubscriptionsRepository = $manager->getRepository(NewsletterSubscription::class);
        $this->committeesRepository = $manager->getRepository(Committee::class);
    }

    /**
     * Aggregate adherents and newsletter subscribers of the given referent managed area
     * into a single collection.
     *
     * @param Adherent $referent
     *
     * @return ManagedUser[]
     */
    public function findUsersManagedBy(Adherent $referent)
    {
        $managedUsers = [];

        foreach ($this->newsletterSubscriptionsRepository->findAllManagedBy($referent) as $subscription) {
            $managedUsers[$subscription->getEmail()] = ManagedUser::createFromNewsletterSubscription($subscription);
        }

        foreach ($this->adherentsRepository->findAllManagedBy($referent) as $adherent) {
            $managedUsers[$adherent->getEmailAddress()] = ManagedUser::createFromAdherent($adherent);
        }

        usort($managedUsers, function (ManagedUser $a, ManagedUser $b) {
            return strnatcmp($a->getPostalCode(), $b->getPostalCode());
        });

        return $managedUsers;
    }

    /**
     * Find all the committees managed by the given referent.
     *
     * @param Adherent $referent
     *
     * @return Committee[]
     */
    public function findCommitteesManagedBy(Adherent $referent)
    {
        return $this->committeesRepository->findAllManagedBy($referent);
    }
}

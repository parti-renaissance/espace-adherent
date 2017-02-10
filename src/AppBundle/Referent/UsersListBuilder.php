<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Doctrine\ORM\EntityManager;

class UsersListBuilder
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
     * Aggregate adherents and newsletter subscribers of the given referent managed area
     * into a single collection.
     *
     * @param Adherent $referent
     *
     * @return ManagedUser[]|ManagedUserCollection
     */
    public function buildManagedUsersListFor(Adherent $referent): ManagedUserCollection
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

        return new ManagedUserCollection($managedUsers);
    }
}

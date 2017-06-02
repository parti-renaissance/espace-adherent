<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\NewsletterSubscription;
use Doctrine\ORM\EntityRepository;

class NewsletterSubscriptionRepository extends EntityRepository
{
    /**
     * Finds the list of newsletter subscribers managed by the given referent.
     *
     * @param Adherent $referent
     *
     * @return NewsletterSubscription[]
     */
    public function findAllManagedBy(Adherent $referent)
    {
        $hasFranceManagedArea = false;
        foreach ($referent->getManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                $hasFranceManagedArea = true;
            }
        }

        if (!$hasFranceManagedArea) {
            return [];
        }

        $qb = $this->createQueryBuilder('n')
            ->select('n')
            ->orderBy('n.createdAt', 'DESC')
            ->where('n.email != :self')
            ->setParameter('self', $referent->getEmailAddress())
            ->andWhere('LENGTH(n.postalCode) = 5');

        $codesFilter = $qb->expr()->orX();

        foreach ($referent->getManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add($qb->expr()->like('n.postalCode', ':code'.$key));
                $qb->setParameter('code'.$key, $code.'%');
            }
        }

        $qb->andWhere($codesFilter);

        return $qb->getQuery()->getResult();
    }
}

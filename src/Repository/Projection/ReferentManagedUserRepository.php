<?php

namespace AppBundle\Repository\Projection;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Referent\ManagedUsersFilter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ReferentManagedUserRepository extends EntityRepository
{
    public function search(Adherent $referent, ManagedUsersFilter $filter = null, bool $onlySubscribers = true): Paginator
    {
        if (!$referent->getManagedArea()) {
            throw new \InvalidArgumentException(sprintf('User %s is not a referent', $referent->getEmailAddress()));
        }

        $qb = $this->createFilterQueryBuilder($referent, $filter);
        if ($onlySubscribers) {
            $qb->andWhere('u.isMailSubscriber = 1');
        }

        $query = $qb->getQuery();
        $query
            ->setFirstResult($filter ? $filter->getOffset() : 0)
            ->setMaxResults(ManagedUsersFilter::PER_PAGE)
            ->useResultCache(true)
            ->setResultCacheLifetime(1800) // 30 minutes
        ;

        return new Paginator($query);
    }

    public function createDispatcherIterator(Adherent $referent, ManagedUsersFilter $filter = null): IterableResult
    {
        if (!$referent->getManagedArea()) {
            throw new \InvalidArgumentException(sprintf('User %s is not a referent', $referent->getEmailAddress()));
        }

        $qb = $this->createFilterQueryBuilder($referent, $filter);
        $qb
            ->addSelect('ABS(u.id - :referentId) AS HIDDEN ref_id_diff')
            ->setParameter('referentId', $referent->getId())
            ->andWhere('u.isMailSubscriber = 1')
            ->orderBy('ref_id_diff', 'ASC')
        ;

        return $qb->getQuery()->iterate();
    }

    private function createFilterQueryBuilder(Adherent $referent, ManagedUsersFilter $filter = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->where('u.status = :status')
            ->setParameter('status', ReferentManagedUser::STATUS_READY)
            ->orderBy('u.createdAt', 'DESC')
        ;

        $codesFilter = $qb->expr()->orX();

        foreach ($referent->getManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'u.type = :adherent'.$key,
                        'u.country = \'FR\'',
                        $qb->expr()->like('u.postalCode', ':code'.$key)
                    )
                );

                $codesFilter->add(
                    $qb->expr()->andX(
                        'u.type = :newsletter'.$key,
                        $qb->expr()->like('u.postalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('adherent'.$key, ReferentManagedUser::TYPE_ADHERENT);
                $qb->setParameter('newsletter'.$key, ReferentManagedUser::TYPE_NEWSLETTER);
                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('u.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);

        if (!$filter) {
            return $qb;
        }

        if ($term = $filter->getQuery()) {
            $qb
                ->andWhere('u.id LIKE :id OR u.firstName LIKE :name OR u.postalCode LIKE :postalCode')
                ->setParameter('id', (int) $term)
                ->setParameter('name', '%'.$term.'%')
                ->setParameter('postalCode', $term.'%')
            ;
        }

        $typeExpression = $qb->expr()->orX();

        if ($filter->includeNewsletter()) {
            $typeExpression->add('u.type = :type_n');
            $qb->setParameter('type_n', ReferentManagedUser::TYPE_NEWSLETTER);
        }

        if ($filter->includeAdherentsNoCommittee()) {
            $typeExpression->add('u.type = :type_anc AND u.isCommitteeMember = 0');
            $qb->setParameter('type_anc', ReferentManagedUser::TYPE_ADHERENT);
        }

        if ($filter->includeAdherentsInCommittee()) {
            $typeExpression->add('u.type = :type_aic AND u.isCommitteeMember = 1');
            $qb->setParameter('type_aic', ReferentManagedUser::TYPE_ADHERENT);
        }

        if ($filter->includeHosts()) {
            $typeExpression->add('u.type = :type_h AND u.isCommitteeHost = 1');
            $qb->setParameter('type_h', ReferentManagedUser::TYPE_ADHERENT);
        }

        $qb->andWhere($typeExpression);

        return $qb;
    }
}

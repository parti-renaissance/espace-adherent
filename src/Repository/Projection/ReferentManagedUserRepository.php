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
    public function search(Adherent $referent, ManagedUsersFilter $filter = null): Paginator
    {
        $qb = $this->createFilterQueryBuilder($referent, $filter);
        $qb->andWhere('u.isMailSubscriber = 1');

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
        $qb = $this->createFilterQueryBuilder($referent, $filter);
        $qb->andWhere('u.isMailSubscriber = 1');

        if ($filter) {
            $qb->setFirstResult($filter->getOffset());
        }

        return $qb->getQuery()->iterate();
    }

    private function createFilterQueryBuilder(Adherent $referent, ManagedUsersFilter $filter = null): QueryBuilder
    {
        if (!$referent->isReferent()) {
            throw new \InvalidArgumentException(sprintf('User %s is not a referent', $referent->getEmailAddress()));
        }

        $qb = $this->createQueryBuilder('u');
        $qb
            ->where('u.status = :status')
            ->setParameter('status', ReferentManagedUser::STATUS_READY)
            ->orderBy('u.createdAt', 'DESC')
        ;

        $tagsFilter = $qb->expr()->orX();

        foreach ($referent->getManagedArea()->getTags() as $key => $tag) {
            $tagsFilter->add("FIND_IN_SET(:tag_$key, u.subscribedTags) > 0");
            $tagsFilter->add(
                $qb->expr()->andX(
                    'u.country = \'FR\'',
                    $qb->expr()->like('u.committeePostalCode', ":tag_prefix_$key")
                )
            );
            $qb->setParameter("tag_$key", $tag->getCode());
            $qb->setParameter("tag_prefix_$key", $tag->getCode().'%');
        }

        $qb->andWhere($tagsFilter);

        if (!$filter) {
            return $qb;
        }

        if ($queryId = $filter->getQueryId()) {
            $queryId = array_map('intval', explode(',', $queryId));

            $idExpression = $qb->expr()->orX();
            foreach ($queryId as $key => $id) {
                $idExpression->add('u.id = :id_'.$key);
                $qb->setParameter('id_'.$key, $id);
            }

            $qb->andWhere($idExpression);
        }

        if ($queryAreaCode = $filter->getQueryAreaCode()) {
            $queryAreaCode = array_map('trim', explode(',', $queryAreaCode));

            $areaCodeExpression = $qb->expr()->orX();
            foreach ($queryAreaCode as $key => $areaCode) {
                if (is_numeric($areaCode)) {
                    $areaCodeExpression->add('u.postalCode LIKE :postalCode_'.$key.' OR u.committeePostalCode LIKE :postalCode_'.$key);
                    $qb->setParameter('postalCode_'.$key, $areaCode.'%');
                }

                if (is_string($areaCode)) {
                    $areaCodeExpression->add('u.country LIKE :countryCode_'.$key);
                    $qb->setParameter('countryCode_'.$key, $areaCode.'%');
                }
            }

            $qb->andWhere($areaCodeExpression);
        }

        if ($queryCity = $filter->getQueryCity()) {
            $queryCity = array_map('trim', explode(',', $queryCity));

            $cityExpression = $qb->expr()->orX();
            foreach ($queryCity as $key => $city) {
                $cityExpression->add('u.city LIKE :city_'.$key);
                $qb->setParameter('city_'.$key, $city.'%');
            }

            $qb->andWhere($cityExpression);
        }

        $typeExpression = $qb->expr()->orX();

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

        if ($filter->includeSupervisors()) {
            $typeExpression->add('u.type = :type_s AND u.isCommitteeSupervisor = 1');
            $qb->setParameter('type_s', ReferentManagedUser::TYPE_ADHERENT);
        }

        $qb->andWhere($typeExpression);

        return $qb;
    }
}

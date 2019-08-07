<?php

namespace AppBundle\Repository\Projection;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator as ApiPaginator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Repository\ReferentTrait;
use AppBundle\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentManagedUserRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReferentManagedUser::class);
    }

    public function search(
        Adherent $referent,
        ManagedUsersFilter $filter = null,
        bool $onlyEmailSubscribers = false,
        int $page = 1
    ): PaginatorInterface {
        $page = $page < 1 ? 1 : $page;

        return new ApiPaginator(new Paginator($this
            ->createFilterQueryBuilder($referent, $filter, $onlyEmailSubscribers)
            ->setFirstResult(($page - 1) * ManagedUsersFilter::PER_PAGE)
            ->setMaxResults(ManagedUsersFilter::PER_PAGE)
            ->getQuery()
            ->useResultCache(true)
            ->setResultCacheLifetime(1800)
        ));
    }

    public function createDispatcherIterator(Adherent $referent, ManagedUsersFilter $filter = null): IterableResult
    {
        $qb = $this->createFilterQueryBuilder($referent, $filter, true);

        if ($filter) {
            $qb->setFirstResult($filter->getOffset());
        }

        return $qb->getQuery()->iterate();
    }

    private function createFilterQueryBuilder(
        Adherent $referent,
        ManagedUsersFilter $filter = null,
        bool $onlyEmailSubscribers = false
    ): QueryBuilder {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('u');
        $qb
            ->where('u.status = :status')
            ->setParameter('status', ReferentManagedUser::STATUS_READY)
        ;

        $sortColumn = 'createdAt';
        $orderDirection = 'DESC';

        if ($filter) {
            if (($sort = $filter->getSort()) && 'lastName' === $sort) {
                $sortColumn = $sort;
            }

            if ($order = $filter->getOrder()) {
                $orderDirection = 'd' === $order ? 'DESC' : 'ASC';
            }
        }

        $qb->orderBy("u.$sortColumn", $orderDirection);

        if ($onlyEmailSubscribers) {
            $qb
                ->andWhere('u.isMailSubscriber = :subscriber')
                ->setParameter(':subscriber', true)
            ;
        }

        $managedAreas = $referent->getManagedAreaTagCodes();
        if ($filter && ($queryZone = $filter->getQueryZone()) && \in_array($queryZone, $managedAreas)) {
            $this->withReferentZoneCondition($qb, [$queryZone]);
        } else {
            $this->withReferentZoneCondition($qb, $managedAreas);
            if (!$filter) {
                return $qb;
            }
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

                if (\is_string($areaCode)) {
                    $areaCodeExpression->add('u.country = :countryOrCity_'.$key.' OR u.city = :countryOrCity_'.$key);
                    $qb->setParameter('countryOrCity_'.$key, $areaCode);
                }
            }

            $qb->andWhere($areaCodeExpression);
        }

        if (\in_array($filter->getQueryGender(), Genders::ALL)) {
            $qb
                ->andWhere('u.gender = :gender')
                ->setParameter('gender', $filter->getQueryGender())
            ;
        }

        if ($queryLastName = $filter->getQueryLastName()) {
            $qb
                ->andWhere('u.lastName LIKE :lastname')
                ->setParameter('lastname', '%'.$queryLastName.'%')
            ;
        }

        if ($queryFirstName = $filter->getQueryFirstName()) {
            $qb
                ->andWhere('u.firstName LIKE :firstName')
                ->setParameter('firstName', '%'.$queryFirstName.'%')
            ;
        }

        if ($queryAgeMinimum = $filter->getQueryAgeMinimum()) {
            $qb
                ->andWhere('u.age >= :ageMinimum')
                ->setParameter('ageMinimum', $queryAgeMinimum)
            ;
        }

        if ($queryAgeMaximum = $filter->getQueryAgeMaximum()) {
            $qb
                ->andWhere('u.age <= :ageMaximum')
                ->setParameter('ageMaximum', $queryAgeMaximum)
            ;
        }

        if ($queryRegisteredFrom = $filter->getQueryRegisteredFrom()) {
            $qb
                ->andWhere('u.createdAt >= :registeredFrom')
                ->setParameter('registeredFrom', $queryRegisteredFrom->format('Y-m-d 00:00:00'))
            ;
        }

        if ($queryRegisteredTo = $filter->getQueryRegisteredTo()) {
            $qb
                ->andWhere('u.createdAt <= :registeredTo')
                ->setParameter('registeredTo', $queryRegisteredTo->format('Y-m-d 23:59:59'))
            ;
        }

        foreach (array_values($filter->getQueryInterests()) as $key => $interest) {
            $qb
                ->andWhere(":interest_$key = ANY_OF(string_to_array(u.interests, ','))")
                ->setParameter('interest_'.$key, $interest)
            ;
        }

        $typeExpression = $qb->expr()->orX();

        if ($filter->includeAdherentsNoCommittee()) {
            $typeExpression->add('u.type = :type_anc AND u.isCommitteeMember = :isCommitteeMember');
            $qb->setParameters([
                'type_anc' => ReferentManagedUser::TYPE_ADHERENT,
                'isCommitteeMember' => false,
            ]);
        }

        if ($filter->includeAdherentsInCommittee()) {
            $typeExpression->add('u.type = :type_aic AND u.isCommitteeMember = :isCommitteeMember_2');
            $qb->setParameters([
                'type_aic' => ReferentManagedUser::TYPE_ADHERENT,
                'isCommitteeMember_2' => true,
            ]);
        }

        if ($filter->includeHosts()) {
            $typeExpression->add('u.type = :type_h AND u.isCommitteeHost = :isCommitteeHost');
            $qb->setParameters([
                'type_h' => ReferentManagedUser::TYPE_ADHERENT,
                'isCommitteeHost' => true,
            ]);
        }

        if ($filter->includeSupervisors()) {
            $and = new Andx();
            $and->add('u.type = :type_s AND u.isCommitteeSupervisor = :isCommitteeSupervisor');
            $qb->setParameters([
                'type_s' => ReferentManagedUser::TYPE_ADHERENT,
                'isCommitteeSupervisor' => true,
            ]);

            $supervisorExpression = $qb->expr()->orX();
            foreach ($referent->getManagedAreaTagCodes() as $key => $code) {
                $supervisorExpression->add(":code_$key = ANY_OF(string_to_array(u.supervisorTags, ','))");
                $qb->setParameter('code_'.$key, $code);
            }

            $and->add($supervisorExpression);
            $typeExpression->add($and);
        }

        if ($filter->includeCitizenProject()) {
            $typeExpression->add('json_length(u.citizenProjectsOrganizer) > 0');
        }

        $qb->andWhere($typeExpression);

        if (null !== $filter->onlyEmailSubscribers()) {
            $qb
                ->andWhere('u.isMailSubscriber = :isMailSubscriber')
                ->setParameter('isMailSubscriber', $filter->onlyEmailSubscribers())
            ;
        }

        return $qb;
    }

    public function countAdherentInReferentZone(Adherent $referent): int
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
        ;

        return (int) $this
            ->withReferentZoneCondition($qb, $referent->getManagedAreaTagCodes())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function withReferentZoneCondition(QueryBuilder $qb, array $referentTags, string $alias = 'u'): QueryBuilder
    {
        $tagsFilter = $qb->expr()->orX();

        foreach ($referentTags as $key => $tag) {
            $tagsFilter->add(":tag_$key = ANY_OF(string_to_array($alias.subscribedTags, ','))");
            $tagsFilter->add(
                $qb->expr()->andX(
                    "$alias.country = 'FR'",
                    $qb->expr()->like("$alias.committeePostalCode", ":tag_prefix_$key")
                )
            );
            $qb->setParameter("tag_$key", $tag);
            $qb->setParameter("tag_prefix_$key", $tag.'%');
        }

        return $qb->andWhere($tagsFilter);
    }
}

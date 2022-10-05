<?php

namespace App\Repository\Projection;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Projection\ManagedUser;
use App\FranceCities\FranceCities;
use App\ManagedUsers\ManagedUsersFilter;
use App\Membership\MembershipSourceEnum;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use App\Repository\ReferentTrait;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ManagedUserRepository extends ServiceEntityRepository
{
    use ReferentTrait;
    use PaginatorTrait;
    use GeoZoneTrait;

    private FranceCities $franceCities;

    public function __construct(ManagerRegistry $registry, FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;

        parent::__construct($registry, ManagedUser::class);
    }

    /**
     * @return ManagedUser[]|PaginatorInterface
     */
    public function searchByFilter(ManagedUsersFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return $this->configurePaginator(
            $this->createFilterQueryBuilder($filter),
            $page,
            $limit,
            static function (Query $query) {
                $query
                    ->useResultCache(true)
                    ->setResultCacheLifetime(1800)
                ;
            }
        );
    }

    public function getExportQueryBuilder(ManagedUsersFilter $filter): Query
    {
        return $this->createFilterQueryBuilder($filter)->getQuery();
    }

    private function createFilterQueryBuilder(ManagedUsersFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->addSelect('zone')
            ->addSelect('parent_zone')
            ->leftJoin('u.zones', 'zone')
            ->leftJoin('zone.parents', 'parent_zone')
            ->where('u.status = :status')
            ->setParameter('status', ManagedUser::STATUS_READY)
            ->orderBy('u.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
        ;

        $this->withGeoZones(
            $filter->getZones() ?: $filter->getManagedZones(),
            $qb,
            'u',
            ManagedUser::class,
            'm2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        if ($queryAreaCode = $filter->getCityAsArray()) {
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

        if ($gender = $filter->getGender()) {
            $qb
                ->andWhere('u.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('u.lastName LIKE :last_name')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('u.firstName LIKE :first_name')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($ageMin = $filter->getAgeMin()) {
            $qb
                ->andWhere('u.age >= :age_min')
                ->setParameter('age_min', $ageMin)
            ;
        }

        if ($ageMax = $filter->getAgeMax()) {
            $qb
                ->andWhere('u.age <= :age_max')
                ->setParameter('age_max', $ageMax)
            ;
        }

        if ($registeredSince = $filter->getRegisteredSince()) {
            $qb
                ->andWhere('u.createdAt >= :registered_since')
                ->setParameter('registered_since', $registeredSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($registeredUntil = $filter->getRegisteredUntil()) {
            $qb
                ->andWhere('u.createdAt <= :registered_until')
                ->setParameter('registered_until', $registeredUntil->format('Y-m-d 23:59:59'))
            ;
        }

        foreach (array_values($filter->getInterests()) as $key => $interest) {
            $qb
                ->andWhere(sprintf('FIND_IN_SET(:interest_%s, u.interests) > 0', $key))
                ->setParameter('interest_'.$key, $interest)
            ;
        }

        if ($committee = $filter->getCommittee()) {
            $qb
                ->andWhere('FIND_IN_SET(:committee_uuid, u.committeeUuids) > 0')
                ->setParameter('committee_uuid', $committee->getUuidAsString())
            ;
        }

        $restrictionsExpression = $qb->expr()->orX();

        if ($committees = $filter->getCommitteeUuids()) {
            $committeesExpression = $qb->expr()->orX();

            foreach ($committees as $key => $uuid) {
                $committeesExpression->add("FIND_IN_SET(:committee_uuid_$key, u.committeeUuids) > 0");
                $qb->setParameter("committee_uuid_$key", $uuid);
            }

            $restrictionsExpression->add($committeesExpression);
        }

        if ($cities = $filter->getCities()) {
            $citiesExpression = $qb->expr()->orX();

            foreach ($cities as $key => $inseeCode) {
                $city = $this->franceCities->getCityByInseeCode($inseeCode);
                $postalCode = $city ? $city->getPostalCode() : null;

                if (!$postalCode) {
                    continue;
                }

                $cityExpression = $qb->expr()->andX(
                    'u.postalCode IN (:city_postalCode_'.$key.')',
                    'u.country = :country_france'
                );
                $qb->setParameter('city_postalCode_'.$key, $postalCode);
                $qb->setParameter('country_france', 'FR');

                $citiesExpression->add($cityExpression);
            }

            $restrictionsExpression->add($citiesExpression);
        }

        if ($restrictionsExpression->count()) {
            $qb->andWhere($restrictionsExpression);
        }

        if (null !== $filter->isCommitteeMember()) {
            $qb->andWhere(sprintf('u.isCommitteeMember = %s', $filter->isCommitteeMember() ? '1' : '0'));
        }

        $typeExpression = $qb->expr()->orX();

        // includes
        if (true === $filter->includeCommitteeHosts()) {
            $typeExpression->add('u.isCommitteeHost = 1');
        }

        if (true === $filter->includeCommitteeSupervisors()) {
            $typeExpression->add('u.isCommitteeSupervisor = 1');
        }

        if (true === $filter->includeCommitteeProvisionalSupervisors()) {
            $typeExpression->add('u.isCommitteeProvisionalSupervisor = 1');
        }

        $qb->andWhere($typeExpression);

        // excludes
        if (false === $filter->includeCommitteeHosts()) {
            $qb->andWhere('u.isCommitteeHost = 0');
        }

        if (false === $filter->includeCommitteeSupervisors()) {
            $qb->andWhere('u.isCommitteeSupervisor = 0');
        }

        if (false === $filter->includeCommitteeProvisionalSupervisors()) {
            $qb->andWhere('u.isCommitteeProvisionalSupervisor = 0');
        }

        if (null !== $filter->getEmailSubscription() && $filter->getSubscriptionType()) {
            $subscriptionTypesCondition = 'FIND_IN_SET(:subscription_type, u.subscriptionTypes) > 0';

            if (false === $filter->getEmailSubscription()) {
                $subscriptionTypesCondition = '(u.subscriptionTypes IS NULL OR FIND_IN_SET(:subscription_type, u.subscriptionTypes) = 0)';
            }

            $qb
                ->andWhere($subscriptionTypesCondition)
                ->setParameter('subscription_type', $filter->getSubscriptionType())
            ;
        }

        if (null !== $filter->getSmsSubscription()) {
            $subscriptionTypesCondition = 'FIND_IN_SET(:sms_subscription_type, u.subscriptionTypes) > 0';

            if (false === $filter->getSmsSubscription()) {
                $subscriptionTypesCondition = '(u.subscriptionTypes IS NULL OR FIND_IN_SET(:sms_subscription_type, u.subscriptionTypes) = 0)';
            }

            $qb
                ->andWhere($subscriptionTypesCondition)
                ->setParameter('sms_subscription_type', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
            ;
        }

        if (null !== $filter->getVoteInCommittee()) {
            $qb->andWhere(sprintf('u.voteCommitteeId %s NULL', $filter->getVoteInCommittee() ? 'IS NOT' : 'IS'));
        }

        if (null !== $filter->getIsCertified()) {
            $qb->andWhere(sprintf('u.certifiedAt %s NULL', $filter->getIsCertified() ? 'IS NOT' : 'IS'));
        }

        if (null !== $filter->getIsRenaissanceMembership()) {
            $qb
                ->andWhere(
                    $filter->getIsRenaissanceMembership()
                        ? 'u.source = :source_renaissance'
                        : 'u.source != :source_renaissance OR u.source IS NULL'
                )
                ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
            ;
        }

        if (null !== $filter->getOnlyJeMengageUsers()) {
            $qb
                ->andWhere(
                    $filter->getOnlyJeMengageUsers()
                        ? 'u.source = :source_jme'
                        : 'u.source != :source_jme OR u.source IS NULL'
                )
                ->setParameter('source_jme', MembershipSourceEnum::JEMENGAGE)
            ;
        }

        return $qb;
    }

    public function countManagedUsers(array $zones = []): int
    {
        if (empty($zones)) {
            throw new \InvalidArgumentException('Zones could not be empty');
        }

        $qb = $this
            ->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->where('u.status = :status')
            ->setParameter('status', ManagedUser::STATUS_READY)
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'u',
            ManagedUser::class,
            'm2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}

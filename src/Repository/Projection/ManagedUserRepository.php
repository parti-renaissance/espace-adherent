<?php

namespace App\Repository\Projection;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Projection\ManagedUser;
use App\Intl\FranceCitiesBundle;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\PaginatorTrait;
use App\Repository\ReferentTagRepository;
use App\Repository\ReferentTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ManagedUserRepository extends ServiceEntityRepository
{
    use ReferentTrait;
    use PaginatorTrait;

    public function __construct(RegistryInterface $registry)
    {
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
            ->where('u.status = :status')
            ->setParameter('status', ManagedUser::STATUS_READY)
            ->orderBy('u.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
        ;

        $this->withZoneCondition($qb, $filter->getReferentTags());

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
                $city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCode);
                $postalCode = $city ? $city['postal_code'] : null;

                if (!$postalCode) {
                    continue;
                }

                $cityExpression = $qb->expr()->andX(
                    'u.postalCode = :city_postalCode_'.$key,
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

        $typeExpression = $qb->expr()->orX();

        if ($filter->includeAdherentsNoCommittee()) {
            $typeExpression->add('u.isCommitteeMember = 0');
        }

        if ($filter->includeAdherentsInCommittee()) {
            $typeExpression->add('u.isCommitteeMember = 1');
        }

        // includes
        if (true === $filter->includeCommitteeHosts()) {
            $typeExpression->add('u.isCommitteeHost = 1');
        }

        if (true === $filter->includeCommitteeSupervisors()) {
            $and = new Andx();
            $and->add('u.isCommitteeSupervisor = 1');

            $supervisorExpression = $qb->expr()->orX();
            foreach ($filter->getReferentTags() as $key => $code) {
                $supervisorExpression->add(sprintf('FIND_IN_SET(:code_%s, u.supervisorTags) > 0', $key));
                $qb->setParameter('code_'.$key, $code->getCode());
            }

            $and->add($supervisorExpression);
            $typeExpression->add($and);
        }

        if (true === $filter->includeCitizenProjectHosts()) {
            $typeExpression->add('json_length(u.citizenProjectsOrganizer) > 0');
        }

        $qb->andWhere($typeExpression);

        // excludes
        if (false === $filter->includeCommitteeHosts()) {
            $qb->andWhere('u.isCommitteeHost = 0');
        }

        if (false === $filter->includeCommitteeSupervisors()) {
            $qb->andWhere('u.isCommitteeSupervisor = 0');
        }

        if (false === $filter->includeCitizenProjectHosts()) {
            $qb->andWhere('u.citizenProjectsOrganizer IS NULL OR json_length(u.citizenProjectsOrganizer) = 0');
        }

        if (null !== $filter->getEmailSubscription() && $filter->getSubscriptionType()) {
            $subscriptionTypesCondition = 'FIND_IN_SET(:subscription_type, u.subscriptionTypes) > 0';
            if (false === $filter->getEmailSubscription()) {
                $subscriptionTypesCondition = '(FIND_IN_SET(:subscription_type, u.subscriptionTypes) = 0 OR u.subscriptionTypes IS NULL)';
            }

            $qb
                ->andWhere($subscriptionTypesCondition)
                ->setParameter('subscription_type', $filter->getSubscriptionType())
            ;
        }

        return $qb;
    }

    public function countManagedUsers(array $referentTags): int
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
        ;

        return (int) $this
            ->withZoneCondition($qb, $referentTags)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function withZoneCondition(QueryBuilder $qb, array $referentTags, string $alias = 'u'): QueryBuilder
    {
        if (1 === \count($referentTags) && ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === ($tag = current($referentTags))->getCode()) {
            return $qb->andWhere("${alias}.country != 'FR'");
        }

        $tagsFilter = $qb->expr()->orX();

        foreach ($referentTags as $key => $tag) {
            $tagsFilter->add("FIND_IN_SET(:tag_$key, $alias.subscribedTags) > 0");
            $tagsFilter->add(
                $qb->expr()->andX(
                    "$alias.country = 'FR'",
                    $qb->expr()->like("$alias.committeePostalCode", ":tag_prefix_$key")
                )
            );
            $qb->setParameter("tag_$key", $tag->getCode());
            $qb->setParameter("tag_prefix_$key", $tag->getCode().'%');
        }

        return $qb->andWhere($tagsFilter);
    }
}

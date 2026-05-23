<?php

declare(strict_types=1);

namespace App\Repository\Projection;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Address\AddressInterface;
use App\Doctrine\Utils\MultiColumnsSearchHelper;
use App\Entity\Projection\ManagedUser;
use App\FranceCities\FranceCities;
use App\ManagedUsers\ManagedUsersFilter;
use App\Membership\MembershipSourceEnum;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ManagedUserRepository extends ServiceEntityRepository
{
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
            useOutputWalkers: false
        );
    }

    public function iterateForExport(ManagedUsersFilter $filter): \Generator
    {
        yield from $this->createExportQueryBuilder($filter)
            ->getQuery()
            ->toIterable([], Query::HYDRATE_ARRAY)
        ;
    }

    private function createFilterQueryBuilder(ManagedUsersFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->addSelect('COALESCE(u.lastMembershipDonation, u.createdAt) as HIDDEN order_column')
            ->orderBy('order_column', 'DESC')
        ;

        return $this->applyFilters($qb, $filter);
    }

    private function createExportQueryBuilder(ManagedUsersFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->addSelect('COALESCE(u.lastMembershipDonation, u.createdAt) as HIDDEN order_column')
            ->orderBy('order_column', 'DESC')
        ;

        return $this->applyFilters($qb, $filter);
    }

    private function applyFilters(QueryBuilder $qb, ManagedUsersFilter $filter): QueryBuilder
    {
        if (!$filter->national && !$filter->hasActivePerimeter()) {
            throw new \LogicException('A non-national scope must define a perimeter (zone, committee or agora) before searching managed users.');
        }

        if ($managedZones = $filter->managedZones) {
            $managedZonesExpression = $qb->expr()->orX();

            foreach ($managedZones as $key => $zone) {
                $managedZonesExpression->add(":managed_zone_$key MEMBER OF u.zones");
                $qb->setParameter("managed_zone_$key", $zone->getId());
            }

            $qb->andWhere($managedZonesExpression);
        }

        if ($selectedZones = $filter->zones) {
            $selectedZonesExpression = $qb->expr()->orX();

            foreach ($selectedZones as $key => $zone) {
                $selectedZonesExpression->add(":selected_zone_$key MEMBER OF u.zones");
                $qb->setParameter("selected_zone_$key", $zone->getId());
            }

            $qb->andWhere($selectedZonesExpression);
        }

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

        if ($filter->searchTerm) {
            MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                $qb,
                $filter->searchTerm,
                [
                    ['u.firstName', 'u.lastName'],
                    ['u.lastName', 'u.firstName'],
                    ['u.email', 'u.email'],
                ],
                ['u.phone']
            );
        }

        if ($gender = $filter->gender) {
            $qb
                ->andWhere('u.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($nationality = $filter->nationality) {
            $qb
                ->andWhere('u.nationality = :nationality')
                ->setParameter('nationality', $nationality)
            ;
        }

        if ($lastName = $filter->lastName) {
            $qb
                ->andWhere('u.lastName LIKE :last_name')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->firstName) {
            $qb
                ->andWhere('u.firstName LIKE :first_name')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($ageMin = $filter->ageMin) {
            $qb
                ->andWhere('u.age >= :age_min')
                ->setParameter('age_min', $ageMin)
            ;
        }

        if ($ageMax = $filter->ageMax) {
            $qb
                ->andWhere('u.age <= :age_max')
                ->setParameter('age_max', $ageMax)
            ;
        }

        if ($registeredSince = $filter->registeredSince) {
            $qb
                ->andWhere('u.createdAt >= :registered_since')
                ->setParameter('registered_since', $registeredSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($registeredUntil = $filter->registeredUntil) {
            $qb
                ->andWhere('u.createdAt <= :registered_until')
                ->setParameter('registered_until', $registeredUntil->format('Y-m-d 23:59:59'))
            ;
        }

        if (null !== $filter->isNewRenaissanceUser) {
            $qb
                ->andWhere(\sprintf('u.createdAt %s :registered_since_last_15d', $filter->isNewRenaissanceUser ? '>=' : '<'))
                ->setParameter('registered_since_last_15d', new \DateTime('-15 days')->setTime(0, 0))
            ;
        }

        if (null !== $filter->isCampusRegistered) {
            $qb
                ->andWhere(\sprintf('u.campusRegisteredAt %s NULL', $filter->isCampusRegistered ? 'IS NOT' : 'IS'))
            ;
        }

        foreach (array_values($filter->interests) as $key => $interest) {
            $qb
                ->andWhere(\sprintf('FIND_IN_SET(:interest_%s, u.interests) > 0', $key))
                ->setParameter('interest_'.$key, $interest)
            ;
        }

        if ($committee = $filter->committee) {
            $qb
                ->andWhere('FIND_IN_SET(:committee_uuid, u.committeeUuids) > 0')
                ->setParameter('committee_uuid', $committee->getUuidAsString())
            ;
        }

        if ($filter->adherentTags) {
            $qb
                ->andWhere('u.tags LIKE :adherent_tag')
                ->setParameter('adherent_tag', '%'.$filter->adherentTags.'%')
            ;
        }

        if ($filter->electTags) {
            $qb
                ->andWhere('u.tags LIKE :elect_tag')
                ->setParameter('elect_tag', '%'.$filter->electTags.'%')
            ;
        }

        if ($filter->staticTags) {
            $staticTag = $filter->staticTags;
            $operator = 'LIKE';
            $nullCheck = '';

            if (str_ends_with($staticTag, '--')) {
                $operator = 'NOT LIKE';
                $nullCheck = ' OR u.tags IS NULL';
                $staticTag = substr($staticTag, 0, -2);
            }

            $qb
                ->andWhere(\sprintf('u.tags %s :static_tag %s', $operator, $nullCheck))
                ->setParameter('static_tag', '%'.$staticTag.'%')
            ;
        }

        $restrictionsExpression = $qb->expr()->orX();

        if ($committees = $filter->committeeUuids) {
            $committeesExpression = $qb->expr()->orX();

            foreach ($committees as $key => $uuid) {
                $committeesExpression->add("FIND_IN_SET(:committee_uuid_$key, u.committeeUuids) > 0");
                $committeesExpression->add("u.committeeUuid = :committee_uuid_$key");
                $qb->setParameter("committee_uuid_$key", $uuid);
            }

            $restrictionsExpression->add($committeesExpression);
        }

        if ($agoras = $filter->agoraUuids) {
            $agoraExpression = $qb->expr()->orX();

            foreach ($agoras as $key => $uuid) {
                $agoraExpression->add("u.agoraUuid = :agora_uuid_$key");
                $qb->setParameter("agora_uuid_$key", $uuid);
            }

            $restrictionsExpression->add($agoraExpression);
        }

        if ($cities = $filter->cities) {
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
                $qb->setParameter('country_france', AddressInterface::FRANCE);

                $citiesExpression->add($cityExpression);
            }

            $restrictionsExpression->add($citiesExpression);
        }

        if ($restrictionsExpression->count()) {
            $qb->andWhere($restrictionsExpression);
        }

        if (null !== $filter->isCommitteeMember) {
            $qb->andWhere(\sprintf('u.isCommitteeMember = %s', $filter->isCommitteeMember ? '1' : '0'));
        }

        $typeExpression = $qb->expr()->orX();

        // includes
        if (true === $filter->includeCommitteeHosts) {
            $typeExpression->add('u.isCommitteeHost = 1');
        }

        if (true === $filter->includeCommitteeSupervisors) {
            $typeExpression->add('u.isCommitteeSupervisor = 1');
        }

        if ($typeExpression->count()) {
            $qb->andWhere($typeExpression);
        }

        // excludes
        if (false === $filter->includeCommitteeHosts) {
            $qb->andWhere('u.isCommitteeHost = 0');
        }

        if (false === $filter->includeCommitteeSupervisors) {
            $qb->andWhere('u.isCommitteeSupervisor = 0');
        }

        if (null !== $filter->emailSubscription && $filter->subscriptionType) {
            $subscriptionTypesCondition = 'FIND_IN_SET(:subscription_type, u.subscriptionTypes) > 0';

            if (false === $filter->emailSubscription) {
                $subscriptionTypesCondition = '(u.subscriptionTypes IS NULL OR FIND_IN_SET(:subscription_type, u.subscriptionTypes) = 0)';
            }

            $qb
                ->andWhere($subscriptionTypesCondition)
                ->setParameter('subscription_type', $filter->subscriptionType)
            ;
        }

        if (null !== $filter->smsSubscription) {
            $subscriptionTypesCondition = 'FIND_IN_SET(:sms_subscription_type, u.subscriptionTypes) > 0';

            if (false === $filter->smsSubscription) {
                $subscriptionTypesCondition = '(u.subscriptionTypes IS NULL OR FIND_IN_SET(:sms_subscription_type, u.subscriptionTypes) = 0)';
            }

            $qb
                ->andWhere($subscriptionTypesCondition)
                ->setParameter('sms_subscription_type', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
            ;
        }

        if (null !== $filter->voteInCommittee) {
            $qb->andWhere(\sprintf('u.voteCommitteeId %s NULL', $filter->voteInCommittee ? 'IS NOT' : 'IS'));
        }

        if (null !== $filter->isCertified) {
            $qb->andWhere(\sprintf('u.certifiedAt %s NULL', $filter->isCertified ? 'IS NOT' : 'IS'));
        }

        if ($lastMembershipSince = $filter->lastMembershipSince) {
            $qb
                ->andWhere('u.lastMembershipDonation >= :last_membership_since')
                ->setParameter('last_membership_since', $lastMembershipSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($lastMembershipBefore = $filter->lastMembershipBefore) {
            $qb
                ->andWhere('u.lastMembershipDonation <= :last_membership_before')
                ->setParameter('last_membership_before', $lastMembershipBefore->format('Y-m-d 23:59:59'))
            ;
        }

        if ($firstMembershipSince = $filter->firstMembershipSince) {
            $qb
                ->andWhere('u.firstMembershipDonation >= :first_membership_since')
                ->setParameter('first_membership_since', $firstMembershipSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($firstMembershipBefore = $filter->firstMembershipBefore) {
            $qb
                ->andWhere('u.firstMembershipDonation <= :first_membership_before')
                ->setParameter('first_membership_before', $firstMembershipBefore->format('Y-m-d 23:59:59'))
            ;
        }

        if (null !== $filter->onlyJeMengageUsers) {
            $qb
                ->andWhere(
                    $filter->onlyJeMengageUsers
                        ? 'u.source = :source_jme'
                        : 'u.source != :source_jme OR u.source IS NULL'
                )
                ->setParameter('source_jme', MembershipSourceEnum::JEMENGAGE)
            ;
        }

        if ($declaredMandates = $filter->declaredMandates) {
            $declaredMandateTypesConditions = $qb->expr()->orX();

            foreach ($declaredMandates as $key => $declaredMandate) {
                $declaredMandateTypesConditions->add("FIND_IN_SET(:declared_mandate_$key, u.declaredMandates) > 0");
                $qb->setParameter("declared_mandate_$key", $declaredMandate);
            }

            $qb->andWhere($declaredMandateTypesConditions);
        }

        if ($electMandates = $filter->electMandates) {
            $electMandatesConditions = $qb->expr()->orX();

            foreach ($electMandates as $key => $electMandate) {
                $electMandatesConditions->add("FIND_IN_SET(:elect_mandate_$key, u.electMandates) > 0");
                $qb->setParameter("elect_mandate_$key", $electMandate);
            }

            $qb->andWhere($electMandatesConditions);
        }

        return $qb;
    }
}

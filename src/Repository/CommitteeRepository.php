<?php

namespace App\Repository;

use App\Address\Address;
use App\Collection\CommitteeCollection;
use App\Coordinator\Filter\CommitteeFilter;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeMembership;
use App\Entity\District;
use App\Entity\Event;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Geocoder\Coordinates;
use App\Intl\FranceCitiesBundle;
use App\Search\SearchParametersFilter;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationZoneEnum;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;
    use NearbyTrait;
    use ReferentTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public const ONLY_APPROVED = 1;
    public const INCLUDE_UNAPPROVED = 2;
    public const DEFAULT_MAX_RESULTS_LIST = 3;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Committee::class);
    }

    public function countElements(): int
    {
        return (int) $this
            ->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.status = :approved')
            ->setParameter('approved', Committee::APPROVED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Finds a Committee instance by its unique canonical name.
     */
    public function findOneByName(string $name): ?Committee
    {
        $canonicalName = Committee::canonicalize($name);

        return $this->findOneBy(['canonicalName' => $canonicalName]);
    }

    public function findOneByUuid(string $uuid): ?Committee
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * Finds approved Committee instances.
     *
     * @return Committee[]
     */
    public function findApprovedCommittees()
    {
        return $this->findBy(['status' => Committee::APPROVED]);
    }

    /**
     * Returns the most recent created Committee.
     */
    public function findMostRecentCommittee(): ?Committee
    {
        $query = $this
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findLastApprovedCommittees(int $count = self::DEFAULT_MAX_RESULTS_LIST): array
    {
        return $this
            ->createQueryBuilder('committee')
            ->where('committee.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('committee.approvedAt', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Committee[]
     */
    public function findNearbyCommittees(Coordinates $coordinates, int $count = self::DEFAULT_MAX_RESULTS_LIST)
    {
        $qb = $this
            ->createNearbyQueryBuilder($coordinates)
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setMaxResults($count)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findNearbyCommitteesFilteredByCountry(
        Coordinates $coordinates,
        string $country,
        string $postalCodePrefix = null,
        int $count = self::DEFAULT_MAX_RESULTS_LIST
    ): array {
        $qb = $this
            ->createNearbyQueryBuilder($coordinates)
            ->andWhere('n.status = :status')
            ->andWhere('n.postAddress.country = :country')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('country', $country)
        ;

        if ($postalCodePrefix) {
            $qb
                ->andWhere('n.postAddress.postalCode LIKE :postalCode')
                ->setParameter('postalCode', $postalCodePrefix.'%')
            ;
        }

        return $qb
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the total number of approved committees.
     */
    public function countApprovedCommittees(): int
    {
        $query = $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function findCommittees(array $uuids): CommitteeCollection
    {
        if (!$uuids) {
            return new CommitteeCollection();
        }

        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->in('c.uuid', $uuids))
            ->andWhere($qb->expr()->neq('c.status', ':status'))
            ->setParameter('status', Committee::REFUSED)
            ->orderBy('c.membersCount', 'DESC')
        ;

        return new CommitteeCollection($qb->getQuery()->getResult());
    }

    public function getQueryBuilderForTags(array $referentTags): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $this->applyGeoFilter($qb, $referentTags, 'c');

        return $qb;
    }

    public function findReferentCommittees(Adherent $referent): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c AS committee')
            ->addSelect('SUM(IF(cm.enableVote = :true, 1, 0)) AS total_voters')
            ->addSelect(sprintf('(%s) AS total_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(candidacy1.id IS NOT NULL AND candidacy1.gender = :male, 1, 0))')
                    ->from(CommitteeCandidacy::class, 'candidacy1')
                    ->innerJoin('candidacy1.committeeElection', 'election1')
                    ->innerJoin('election1.designation', 'designation1')
                    ->where('election1.committee = c AND designation1.candidacyStartDate <= :now AND :now <= designation1.voteEndDate')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(candidacy2.id IS NOT NULL AND candidacy2.gender = :female, 1, 0))')
                    ->from(CommitteeCandidacy::class, 'candidacy2')
                    ->innerJoin('candidacy2.committeeElection', 'election2')
                    ->innerJoin('election2.designation', 'designation2')
                    ->where('election2.committee = c AND designation2.candidacyStartDate <= :now AND :now <= designation2.voteEndDate')
                    ->getDQL()
            ))
            ->where('c.status = :status')
            ->leftJoin(CommitteeMembership::class, 'cm', Join::WITH, 'cm.committee = c')
            ->setParameters([
                'status' => Committee::APPROVED,
                'male' => Genders::MALE,
                'female' => Genders::FEMALE,
                'now' => new \DateTime(),
                'true' => true,
            ])
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
            ->groupBy('c.id')
        ;

        $this->applyReferentGeoFilter($qb, $referent, 'c');

        return $qb->getQuery()->getResult();
    }

    public function findManagedBy(Adherent $referent): array
    {
        if (!$referent->isReferent()) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $this->applyReferentGeoFilter($qb, $referent, 'c');

        return $qb->getQuery()->getResult();
    }

    public function findManagedByCoordinator(Adherent $coordinator, CommitteeFilter $filter): array
    {
        if (!$coordinator->isCoordinatorCommitteeSector()) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $filter->setCoordinator($coordinator);
        $filter->apply($qb, 'c');

        return $qb->getQuery()->getResult();
    }

    public function countSitemapCommittees(): int
    {
        return (int) $this->createSitemapQb()
            ->select('COUNT(c) AS nb')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findSitemapCommittees(int $page, int $perPage): array
    {
        return $this->createSitemapQb()
            ->select('c.uuid', 'c.slug')
            ->orderBy('c.id')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    private function createSitemapQb(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
        ;
    }

    /**
     * @return Committee[]
     */
    public function searchCommittees(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this
                ->createNearbyQueryBuilder($coordinates)
                ->andWhere($this->getNearbyExpression().' < :distance_max')
                ->setParameter('distance_max', $search->getRadius())
            ;
        } else {
            $qb = $this->createQueryBuilder('n');
        }

        if (!empty($query = $search->getQuery())) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        return $qb
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasCommitteeInStatus(Adherent $adherent, array $status): bool
    {
        $nb = $this->createQueryBuilder('c')
            ->select('COUNT(c) AS nb')
            ->where('c.createdBy = :creator')
            ->andWhere('c.status IN (:status)')
            ->setParameter('creator', $adherent->getUuid()->toString())
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $nb > 0;
    }

    public function findCommitteesUuidByCreatorUuids(array $creatorsUuid): array
    {
        $qb = $this->createQueryBuilder('c');

        $query = $qb
            ->select('c.uuid')
            ->where('c.createdBy IN (:creatorsUuid)')
            ->setParameter('creatorsUuid', $creatorsUuid)
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    public function findByPartialName(string $search, int $limit = 10): array
    {
        return $this
            ->createPartialNameQueryBuilder($search, 'committee')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPartialNameForReferent(Adherent $referent, string $search, int $limit = 10): array
    {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->applyReferentGeoFilter($qb, $referent, $alias);

        return $qb->getQuery()->getResult();
    }

    public function findByPartialNameForDeputy(Adherent $deputy, string $search, int $limit = 10): array
    {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->applyDeputyGeoFilter($qb, $deputy, $alias);

        return $qb->getQuery()->getResult();
    }

    private function createPartialNameQueryBuilder(string $search, string $alias = 'c'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->where("$alias.canonicalName LIKE :search")
            ->andWhere("$alias.status = :status")
            ->setParameter('search', '%'.strtolower($search).'%')
            ->setParameter('status', Committee::APPROVED)
        ;
    }

    public function paginateAllApprovedCommittees(
        int $offset = 0,
        int $limit = SearchParametersFilter::DEFAULT_MAX_RESULTS
    ): Paginator {
        $query = $this->createQueryBuilder('e')
            ->andWhere('e.status = :approved')
            ->setParameter('approved', Committee::APPROVED)
            ->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return new Paginator($query);
    }

    public function countApprovedForReferent(Adherent $referent): int
    {
        return (int) $this->createQueryBuilder('committee')
            ->select('COUNT(committee) AS count')
            ->join('committee.referentTags', 'tag')
            ->where('committee.status = :status')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findApprovedForReferentAutocomplete(Adherent $referent, $value): array
    {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('committee')
            ->select('committee.uuid, committee.name')
            ->join('committee.referentTags', 'tag')
            ->where('committee.status = :status')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->orderBy('committee.name')
        ;

        if ($value) {
            $qb
                ->andWhere('committee.name LIKE :searchedName')
                ->setParameter('searchedName', $value.'%')
                ->setMaxResults(70)
            ;
        }

        return array_map(function (array $committee) {
            return [$committee['uuid'] => $committee['name']];
        }, $qb->getQuery()->getScalarResult());
    }

    public function findCitiesForReferentAutocomplete(Adherent $referent, $value): array
    {
        $this->checkReferent($referent);

        $qb = $this->createQueryBuilder('committee')
            ->select('DISTINCT committee.postAddress.cityName as city')
            ->join('committee.referentTags', 'tag')
            ->where('committee.status = :status')
            ->andWhere('tag.id IN (:tags)')
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->orderBy('city')
        ;

        if ($value) {
            $qb
                ->andWhere('committee.postAddress.cityName LIKE :searchedCityName')
                ->setParameter('searchedCityName', $value.'%')
            ;
        }

        return array_column($qb->getQuery()->getArrayResult(), 'city');
    }

    public function retrieveMostActiveCommitteesInReferentManagedArea(Adherent $referent, int $limit = 5): array
    {
        return $this->retrieveTopCommitteesInReferentManagedArea($referent, $limit);
    }

    public function retrieveLeastActiveCommitteesInReferentManagedArea(Adherent $referent, int $limit = 5): array
    {
        return $this->retrieveTopCommitteesInReferentManagedArea($referent, $limit, false);
    }

    /**
     * Finds committees in the district.
     *
     * @return Committee[]
     */
    public function findAllInDistrict(District $district): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin(District::class, 'd', Join::WITH, 'd.id = :district_id')
            ->innerJoin('d.geoData', 'gd')
            ->where("ST_Within(ST_GeomFromText(CONCAT('POINT(',c.postAddress.longitude,' ',c.postAddress.latitude,')')), gd.geoShape) = 1")
            ->andWhere('c.status = :status')
            ->setParameter('district_id', $district->getId())
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCommitteesByPrivilege(Adherent $adherent, array $privilege): array
    {
        // Prevent SQL query if the adherent doesn't follow any committees yet.
        if (0 === \count($adherent->getMemberships())) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->innerJoin(CommitteeMembership::class, 'cm', Join::WITH, 'c = cm.committee')
            ->where('cm.privilege IN (:privilege)')
            ->andWhere('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->setParameter('privilege', $privilege)
            ->getQuery()
            ->getResult()
        ;
    }

    private function retrieveTopCommitteesInReferentManagedArea(
        Adherent $referent,
        int $limit = 5,
        bool $mostActive = true
    ): array {
        $this->checkReferent($referent);

        $result = $this->createQueryBuilder('committee')
            ->select('committee.name, COUNT(event) AS events, SUM(event.participantsCount) as participants')
            ->join(Event::class, 'event', Join::WITH, 'event.committee = committee.id')
            ->join('committee.referentTags', 'tag')
            ->where('tag.id IN (:tags)')
            ->andWhere('committee.status = :status')
            ->andWhere('event.beginAt >= :from')
            ->andWhere('event.beginAt < :until')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('from', (new Chronos('first day of this month'))->setTime(0, 0, 0))
            ->setParameter('until', (new Chronos('first day of next month'))->setTime(0, 0, 0))
            ->setMaxResults($limit)
            ->orderBy('events', $mostActive ? 'DESC' : 'ASC')
            ->addOrderBy('participants', $mostActive ? 'DESC' : 'ASC')
            ->addOrderBy('committee.id', 'ASC')
            ->groupBy('committee.id')
            ->getQuery()
            ->getArrayResult()
        ;

        return $this->removeParticipantionsCountAndId($result);
    }

    private function removeParticipantionsCountAndId(array $committees): array
    {
        array_walk($committees, function (&$item) {
            unset($item['participants']);
        });

        return $committees;
    }

    /**
     * @return Committee[]
     */
    public function findAllWithoutStartedElection(Designation $designation, int $offset = 0, int $limit = 200): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.committeeElection', 'el')
            ->where('el IS NULL AND c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->groupBy('c')
        ;

        if (DesignationZoneEnum::toArray() !== array_intersect(DesignationZoneEnum::toArray(), $designation->getZones())) {
            $zoneCondition = new Orx();

            // Outre-Mer condition
            $zoneCondition->add(sprintf(
                'c.postAddress.country = :fr AND SUBSTRING(c.postAddress.postalCode, 1, 3) %s (:outremer_codes)',
                \in_array(DesignationZoneEnum::OUTRE_MER, $designation->getZones(), true) ? 'IN' : 'NOT IN'
            ));

            // France vs FDE
            if ([DesignationZoneEnum::FRANCE, DesignationZoneEnum::FDE] !== array_intersect([DesignationZoneEnum::FRANCE, DesignationZoneEnum::FDE], $designation->getZones())) {
                $zoneCondition->add(sprintf(
                    'c.postAddress.country %s :fr',
                    \in_array(DesignationZoneEnum::FRANCE, $designation->getZones(), true) ? '=' : '!='
                ));
            }

            $qb
                ->andWhere($zoneCondition)
                ->setParameter('fr', Address::FRANCE)
                ->setParameter('outremer_codes', array_keys(FranceCitiesBundle::DOMTOM_INSEE_CODE))
            ;
        }

        return $qb->getQuery()->getResult();
    }
}

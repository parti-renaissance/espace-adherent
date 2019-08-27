<?php

namespace AppBundle\Repository;

use AppBundle\Collection\CommitteeCollection;
use AppBundle\Coordinator\Filter\CommitteeFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\District;
use AppBundle\Entity\Event;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Search\SearchParametersFilter;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
        return $this->createQueryBuilder('committee')
            ->where('committee.canonicalName LIKE :search')
            ->andWhere('committee.status = :status')
            ->setParameter('search', '%'.strtolower($search).'%')
            ->setParameter('status', Committee::APPROVED)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function unfollowCommitteesOnUnregistration(Adherent $adherent): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.membersCount', 'c.membersCount - 1')
            ->where('c.uuid IN (:uuids)')
            ->setParameter('uuids', $adherent->getMemberships()->getCommitteeUuids())
            ->getQuery()
            ->execute()
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
            ->where("ST_Within(ST_GeomFromText(CONCAT('POINT(',c.postAddress.longitude,' ',c.postAddress.latitude,')')), gd.geoShape) = :within")
            ->andWhere('c.status = :status')
            ->setParameters([
                'district_id' => $district->getId(),
                'within' => true,
                'status' => Committee::APPROVED,
            ])
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
}

<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Address\Address;
use App\Address\AddressInterface;
use App\Committee\Filter\CommitteeDesignationsListFilter;
use App\Committee\Filter\CommitteeListFilter;
use App\Coordinator\Filter\CommitteeFilter;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use App\Geocoder\Coordinates;
use App\Intl\FranceCitiesBundle;
use App\Search\SearchParametersFilter;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationGlobalZoneEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class CommitteeRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use NearbyTrait;
    use GeoZoneTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public const DEFAULT_MAX_RESULTS_LIST = 3;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Committee::class);
    }

    public function countElements(): int
    {
        return (int) $this
            ->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->where('c.status = :approved AND c.version = 1')
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

    /**
     * @return Committee[]
     */
    public function findApprovedByAddress(Address $address, ?int $limit = null): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.postAddress.address = :address AND c.postAddress.postalCode = :postal_code')
            ->andWhere('c.postAddress.cityName LIKE :city_name AND c.postAddress.country = :country')
            ->andWhere('c.status = :approved')
            ->setParameters([
                'address' => $address->getAddress(),
                'postal_code' => $address->getPostalCode(),
                'city_name' => $address->getCityName().'%',
                'country' => $address->getCountry(),
                'approved' => Committee::APPROVED,
            ])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
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
        return $this->findBy(['status' => Committee::APPROVED, 'version' => 1]);
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

    public function getQueryBuilderForZones(array $zones): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        return $qb;
    }

    /**
     * @return Committee[]|PaginatorInterface
     */
    public function searchByFilter(CommitteeListFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return $this->configurePaginator($this->createFilterQueryBuilder($filter), $page, $limit);
    }

    /**
     * @return Committee[]|PaginatorInterface
     */
    public function searchRequestsByFilter(
        CommitteeListFilter $filter,
        int $page = 1,
        int $limit = 100
    ): PaginatorInterface {
        $queryBuilder = $this->createRequestsFilterQueryBuilder($filter->getZones() ?: $filter->getManagedZones())
            ->orderBy('c.createdAt', 'DESC')
            ->groupBy('c.id')
        ;

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    /**
     * @param Zone[] $zones
     */
    public function countRequestsForZones(array $zones, ?string $status = null): int
    {
        $qb = $this
            ->createRequestsFilterQueryBuilder($zones)
            ->select('COUNT(DISTINCT c.id)')
        ;

        if ($status) {
            $qb
                ->andWhere('c.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function updateMembershipsCounters(): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            'UPDATE committees AS c
            INNER JOIN (
                SELECT
                    c2.id,
                    SUM(IF(a.last_membership_donation IS NOT NULL, 1, 0)) AS members_count,
                    SUM(IF(a.last_membership_donation IS NULL AND a.source IS NOT NULL, 1, 0)) AS sympathizers_count,
                    SUM(IF(a.last_membership_donation IS NULL AND a.source IS NULL, 1, 0)) AS members_em_count
                FROM committees c2
                INNER JOIN committees_memberships cm ON cm.committee_id = c2.id
                INNER JOIN adherents a ON a.id = cm.adherent_id
                WHERE c2.version = 2
                GROUP BY c2.id
            ) AS t ON t.id = c.id
            SET
                c.members_count = t.members_count,
                c.members_em_count = t.members_em_count,
                c.sympathizers_count = t.sympathizers_count'
        );
    }

    /**
     * @param Zone[] $zones
     */
    public function countForZones(array $zones): int
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->where('c.status = :status AND c.version = 1')
            ->setParameters([
                'status' => Committee::APPROVED,
            ])
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'c',
            Committee::class,
            'c2',
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

    public function findManagedByCoordinator(Adherent $coordinator, CommitteeFilter $filter): array
    {
        if (!$coordinator->isRegionalCoordinator()) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.name', 'ASC')
            ->orderBy('c.createdAt', 'DESC')
            ->where('c.version = 1')
        ;

        $this->withGeoZones(
            $coordinator->getRegionalCoordinatorZone(),
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2'
        );

        $filter->apply($qb, 'c');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Committee[]
     */
    public function searchCommittees(SearchParametersFilter $search): array
    {
        $alias = 'n';

        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this
                ->createNearbyQueryBuilder($coordinates)
                ->andWhere($this->getNearbyExpression($alias).' < :distance_max')
                ->setParameter('distance_max', $search->getRadius())
            ;
        } else {
            $qb = $this->createQueryBuilder($alias);
        }

        if (!empty($query = $search->getQuery())) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        return $qb
            ->andWhere('n.status = :status AND n.version = 1')
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

    public function findByPartialNameForDeputy(Adherent $deputy, string $search, int $limit = 10): array
    {
        $qb = $this
            ->createPartialNameQueryBuilder($search, $alias = 'committee')
            ->setMaxResults($limit)
        ;

        $this->withGeoZones(
            [$deputy->getDeputyZone()],
            $qb,
            $alias,
            Committee::class,
            'c2',
            'zones',
            'z2'
        );

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
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.status = :approved AND c.version = 1')
            ->setParameter('approved', Committee::APPROVED)
            ->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return new Paginator($query);
    }

    public function createQueryBuilderForZones(array $zones, int $version, bool $withZoneParents = true): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.status = :status AND c.version = :version')
            ->setParameters([
                'status' => Committee::APPROVED,
                'version' => $version,
            ])
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('c.createdAt', 'DESC')
        ;

        return $this->withGeoZones(
            $zones,
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2',
            fn (QueryBuilder $queryBuilder, string $entityClassAlias) => $queryBuilder->andWhere($entityClassAlias.'.status = :status'),
            $withZoneParents
        );
    }

    /**
     * @return Committee[]
     */
    public function findInZones(array $zones, int $version = 2, bool $withZoneParents = true): array
    {
        if (!$zones) {
            return [];
        }

        return $this->createQueryBuilderForZones($zones, $version, $withZoneParents)->getQuery()->getResult();
    }

    public function findInAdherentZone(Adherent $adherent): array
    {
        return $this->findInZones($adherent->getParentZonesOfType($adherent->isForeignResident() ? Zone::CUSTOM : Zone::DEPARTMENT));
    }

    public function findCommitteesForHost(Adherent $adherent): array
    {
        // Prevent SQL query if the adherent doesn't follow any committees yet.
        if (0 === \count($adherent->getMemberships())) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->innerJoin(CommitteeMembership::class, 'cm', Join::WITH, 'c = cm.committee')
            ->leftJoin('c.adherentMandates', 'am')
            ->where((new Orx())
                ->add('cm.privilege = :privilege AND cm.adherent = :adherent')
                ->add('am.adherent = :adherent AND am.committee IS NOT NULL AND am.quality = :supervisor AND am.finishAt IS NULL')
            )
            ->setParameter('adherent', $adherent)
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Committee[]
     */
    public function findAllWithoutStartedElection(Designation $designation, int $offset = 0, int $limit = 200): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.currentDesignation', 'd')
            ->where('(c.currentDesignation IS NULL OR d.isCanceled = true OR (d.voteEndDate IS NOT NULL AND d.voteEndDate < :date))')
            ->andWhere('c.status = :status')
            ->setParameters([
                'status' => Committee::APPROVED,
                'date' => $designation->getCandidacyStartDate(),
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->groupBy('c.id')
        ;

        if ($identifier = $designation->getElectionEntityIdentifier()) {
            $qb
                ->andWhere('c.uuid = :committee_uuid')
                ->setParameter('committee_uuid', $identifier)
            ;
        } elseif (DesignationGlobalZoneEnum::toArray() !== array_intersect(DesignationGlobalZoneEnum::toArray(), $designation->getGlobalZones())) {
            $zoneCondition = new Orx();

            // Outre-Mer condition
            if (\in_array(DesignationGlobalZoneEnum::OUTRE_MER, $designation->getGlobalZones(), true) || \in_array(DesignationGlobalZoneEnum::FRANCE, $designation->getGlobalZones(), true)) {
                $zoneCondition->add(sprintf(
                    'c.postAddress.country = :fr AND SUBSTRING(c.postAddress.postalCode, 1, 3) %s (:outremer_codes)',
                    \in_array(DesignationGlobalZoneEnum::OUTRE_MER, $designation->getGlobalZones(), true) ? 'IN' : 'NOT IN'
                ));
                $qb->setParameter('outremer_codes', array_keys(FranceCitiesBundle::DOMTOM_INSEE_CODE));
            }

            // France vs FDE
            if ([DesignationGlobalZoneEnum::FRANCE, DesignationGlobalZoneEnum::FDE] !== array_intersect([DesignationGlobalZoneEnum::FRANCE, DesignationGlobalZoneEnum::FDE], $designation->getGlobalZones())) {
                $zoneCondition->add(sprintf(
                    'c.postAddress.country %s :fr',
                    \in_array(DesignationGlobalZoneEnum::FRANCE, $designation->getGlobalZones(), true) ? '=' : '!='
                ));
            }

            $qb
                ->andWhere($zoneCondition)
                ->setParameter('fr', AddressInterface::FRANCE)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findForAdherentWithCommitteeMandates(Adherent $adherent): array
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin('committee.adherentMandates', 'mandate')
            ->where('mandate.adherent = :adherent')
            ->andWhere('mandate.quality IS NULL AND mandate.finishAt IS NULL')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    private function createFilterQueryBuilder(CommitteeListFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('c AS committee')
            ->addSelect(sprintf('(%s) AS total_voters',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('COUNT(DISTINCT cm.id)')
                    ->from(CommitteeMembership::class, 'cm')
                    ->where('cm.committee = c AND cm.enableVote = :true')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(candidacy1.id IS NOT NULL AND candidacy1.gender = :male, 1, 0))')
                    ->from(CommitteeElection::class, 'election1')
                    ->leftJoin('election1.candidacies', 'candidacy1')
                    ->innerJoin('election1.designation', 'designation1')
                    ->where('election1.committee = c AND designation1.candidacyStartDate <= :now')
                    ->andWhere('(designation1.voteEndDate IS NULL OR :now <= designation1.voteEndDate)')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(candidacy2.id IS NOT NULL AND candidacy2.gender = :female, 1, 0))')
                    ->from(CommitteeElection::class, 'election2')
                    ->leftJoin('election2.candidacies', 'candidacy2')
                    ->innerJoin('election2.designation', 'designation2')
                    ->where('election2.committee = c AND designation2.candidacyStartDate <= :now')
                    ->andWhere('(designation2.voteEndDate IS NULL OR :now <= designation2.voteEndDate)')
                    ->getDQL()
            ))
            ->where('c.status = :status AND c.version = 1')
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

        $this->withGeoZones(
            $filter->getZones() ?: $filter->getManagedZones(),
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        return $qb;
    }

    private function createRequestsFilterQueryBuilder(array $zones): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->where('c.createdAt > :from AND c.version = 1')
            ->setParameter('from', new \DateTime('2021-01-01 00:00:00'))
        ;

        $this->withGeoZones(
            $zones,
            $qb,
            'c',
            Committee::class,
            'c2',
            'zones',
            'z2',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(sprintf('%s.createdAt > :from', $entityClassAlias));
            }
        );

        return $qb;
    }

    public function findAvailableForPartials(
        CommitteeDesignationsListFilter $filter,
        int $page = 1,
        int $limit = 100
    ): array {
        $qb = $this->createQueryBuilder('committee')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality IS NULL THEN 1 ELSE 0 END) AS total_designed_adherents')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality IS NULL AND mandate.gender = :female THEN 1 ELSE 0 END) AS total_designed_adherents_female')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality IS NULL AND mandate.gender = :male THEN 1 ELSE 0 END) AS total_designed_adherents_male')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality = :supervisor AND mandate.provisional = :false THEN 1 ELSE 0 END) AS total_supervisors')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality = :supervisor AND mandate.provisional = :false AND mandate.gender = :female THEN 1 ELSE 0 END) AS total_supervisors_female')
            ->addSelect('SUM(CASE WHEN mandate.id IS NOT NULL AND mandate.quality = :supervisor AND mandate.provisional = :false AND mandate.gender = :male THEN 1 ELSE 0 END) AS total_supervisors_male')
            ->leftJoin('committee.adherentMandates', 'mandate', Join::WITH, 'mandate.finishAt IS NULL')
            ->leftJoin('committee.currentDesignation', 'designation')
            ->where('committee.status = :status')
            ->andWhere('committee.approvedAt <= :d30')
            ->andWhere('(designation IS NULL OR designation.voteEndDate < :now)')
            ->groupBy('committee.id')
            ->orderBy('committee.membersEmCount', 'DESC')
            ->setParameters([
                'female' => Genders::FEMALE,
                'male' => Genders::MALE,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
                'false' => false,
                'status' => Committee::APPROVED,
                'now' => new \DateTime(),
                'd30' => (new \DateTime())->modify('-30 days'),
            ])
            ->having('total_designed_adherents < 2 OR total_supervisors < 2')
        ;

        if ($filter->getCommitteeName()) {
            $qb
                ->andWhere('committee.name LIKE :committee_name')
                ->setParameter('committee_name', '%'.$filter->getCommitteeName().'%')
            ;
        }

        if ($filter->getZones()) {
            $this->withGeoZones(
                $filter->getZones(),
                $qb,
                'committee',
                Committee::class,
                'c2',
                'zones',
                'z2',
                function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                    $zoneQueryBuilder->andWhere(sprintf('%1$s.status = :status AND %1$s.approvedAt <= :d30', $entityClassAlias));
                }
            );
        }

        return $qb
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCommitteeForRecentCandidate(Designation $designation, Adherent $adherent): ?Committee
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin('committee.committeeElections', 'election')
            ->innerJoin('election.designation', 'designation', Join::WITH, 'designation.type = :designation_type')
            ->innerJoin('election.candidacies', 'candidacy', Join::WITH, 'candidacy.status = :candidacy_status AND candidacy.createdAt >= :candidacy_date')
            ->innerJoin('candidacy.committeeMembership', 'membership', Join::WITH, 'membership.adherent = :adherent')
            ->where('committee.status = :status')
            ->setParameters([
                'designation_type' => $designation->getType(),
                'candidacy_status' => CandidacyInterface::STATUS_CONFIRMED,
                'adherent' => $adherent,
                'candidacy_date' => new \DateTime('-3 months'),
                'status' => Committee::APPROVED,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findCommitteeForRecentVote(Designation $designation, Adherent $adherent): ?Committee
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin(ElectionEntity::class, 'election_entity', Join::WITH, 'election_entity.committee = committee')
            ->innerJoin('election_entity.election', 'election')
            ->innerJoin('election.designation', 'designation', Join::WITH, 'designation.type = :designation_type')
            ->innerJoin('election.electionRounds', 'election_round')
            ->innerJoin(Voter::class, 'voter', Join::WITH, 'voter.adherent = :adherent')
            ->innerJoin(Vote::class, 'vote', Join::WITH, 'vote.voter = voter AND vote.electionRound = election_round AND vote.votedAt >= :vote_date')
            ->where('committee.status = :status')
            ->setParameters([
                'designation_type' => $designation->getType(),
                'adherent' => $adherent,
                'vote_date' => new \DateTime('-3 months'),
                'status' => Committee::APPROVED,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

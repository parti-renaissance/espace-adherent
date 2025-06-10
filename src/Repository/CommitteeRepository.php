<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Address\Address;
use App\Address\AddressInterface;
use App\Adherent\Tag\TagEnum;
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
use App\Intl\FranceCitiesBundle;
use App\Search\SearchParametersFilter;
use App\Utils\GeometryUtils;
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

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Committee::class);
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
                $zoneQueryBuilder->andWhere(\sprintf('%s.status = :status', $entityClassAlias));
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

    public function updateMembershipsCounters(): void
    {
        $this->getEntityManager()->getConnection()->executeQuery(
            'UPDATE committees AS c
            INNER JOIN (
                SELECT
                    c2.id,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS members_count,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS adherents_count,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS sympathizers_count,
                    SUM(IF(a.tags LIKE ?, 1, 0)) AS members_em_count
                FROM committees c2
                INNER JOIN committees_memberships cm ON cm.committee_id = c2.id
                INNER JOIN adherents a ON a.id = cm.adherent_id
                GROUP BY c2.id
            ) AS t ON t.id = c.id
            SET
                c.members_count = t.members_count,
                c.members_em_count = t.members_em_count,
                c.sympathizers_count = t.sympathizers_count',
            [
                TagEnum::ADHERENT.'%',
                TagEnum::getAdherentYearTag().'%',
                TagEnum::SYMPATHISANT.'%',
                TagEnum::SYMPATHISANT_COMPTE_EM.'%',
            ]
        );
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
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
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

    public function paginateAllApprovedCommittees(
        int $offset = 0,
        int $limit = SearchParametersFilter::DEFAULT_MAX_RESULTS,
    ): Paginator {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.status = :approved')
            ->setParameter('approved', Committee::APPROVED)
            ->getQuery()
            ->setMaxResults($limit)
            ->setFirstResult($offset)
        ;

        return new Paginator($query);
    }

    public function createQueryBuilderForZones(array $zones, bool $withZoneParents = true): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameters([
                'status' => Committee::APPROVED,
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
    public function findInZones(array $zones, bool $withZoneParents = true): array
    {
        if (!$zones) {
            return [];
        }

        return $this->createQueryBuilderForZones($zones, $withZoneParents)->getQuery()->getResult();
    }

    public function findInAdherentZone(Adherent $adherent): array
    {
        return $this->findInZones(array_filter([$adherent->getAssemblyZone()]));
    }

    public function findCommitteesForHost(Adherent $adherent): array
    {
        // Prevent SQL query if the adherent doesn't follow any committees yet.
        if (!$adherent->getCommitteeMembership()) {
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
                $zoneCondition->add(\sprintf(
                    'c.postAddress.country = :fr AND SUBSTRING(c.postAddress.postalCode, 1, 3) %s (:outremer_codes)',
                    \in_array(DesignationGlobalZoneEnum::OUTRE_MER, $designation->getGlobalZones(), true) ? 'IN' : 'NOT IN'
                ));
                $qb->setParameter('outremer_codes', array_keys(FranceCitiesBundle::DOMTOM_INSEE_CODE));
            }

            // France vs FDE
            if ([DesignationGlobalZoneEnum::FRANCE, DesignationGlobalZoneEnum::FDE] !== array_intersect([DesignationGlobalZoneEnum::FRANCE, DesignationGlobalZoneEnum::FDE], $designation->getGlobalZones())) {
                $zoneCondition->add(\sprintf(
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

    private function createFilterQueryBuilder(CommitteeListFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('c AS committee')
            ->addSelect(\sprintf('(%s) AS total_voters',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('COUNT(DISTINCT cm.id)')
                    ->from(CommitteeMembership::class, 'cm')
                    ->where('cm.committee = c AND cm.enableVote = :true')
                    ->getDQL()
            ))
            ->addSelect(\sprintf('(%s) AS total_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(candidacy1.id IS NOT NULL AND candidacy1.gender = :male, 1, 0))')
                    ->from(CommitteeElection::class, 'election1')
                    ->leftJoin('election1.candidacies', 'candidacy1')
                    ->innerJoin('election1.designation', 'designation1')
                    ->where('election1.committee = c AND designation1.candidacyStartDate <= :now')
                    ->andWhere('(designation1.voteEndDate IS NULL OR :now <= designation1.voteEndDate)')
                    ->getDQL()
            ))
            ->addSelect(\sprintf('(%s) AS total_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(candidacy2.id IS NOT NULL AND candidacy2.gender = :female, 1, 0))')
                    ->from(CommitteeElection::class, 'election2')
                    ->leftJoin('election2.candidacies', 'candidacy2')
                    ->innerJoin('election2.designation', 'designation2')
                    ->where('election2.committee = c AND designation2.candidacyStartDate <= :now')
                    ->andWhere('(designation2.voteEndDate IS NULL OR :now <= designation2.voteEndDate)')
                    ->getDQL()
            ))
            ->where('c.status = :status')
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
                $zoneQueryBuilder->andWhere(\sprintf('%s.status = :status', $entityClassAlias));
            }
        );

        return $qb;
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

    public function getCommitteesPerimeters(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.id')
            ->addSelect('c.name')
            ->addSelect('animator.firstName')
            ->addSelect('animator.lastName')
            ->addSelect('animator.id as animatorId')
            ->addSelect('animator.emailAddress')
            ->addSelect('c.membersCount')
            ->addSelect('c.sympathizersCount')
            ->innerJoin(Zone::class, 'z')
            ->innerJoin('z.geoData', 'gd')
            ->addSelect('ST_AsText(st_simplify(gd.geoShape, 0.0001)) AS shape')
            ->andWhere(\sprintf('z.id IN (%s)', $this
                ->createQueryBuilder('c2')
                ->select('DISTINCT COALESCE(zc.id, IF(zp.type IN (:city_child_types), -1, zp.id))')
                ->innerJoin('c2.zones', 'zp')
                ->leftJoin('zp.children', 'zc', Join::WITH, 'zp.type IN (:city_child_types) AND zc.type = :city')
                ->where('c2.id = c.id')
                ->getDQL()
            ))
            ->leftJoin('c.animator', 'animator')
            ->setParameters([
                'city' => Zone::CITY,
                'city_child_types' => [Zone::CANTON, Zone::CITY_COMMUNITY],
            ])
            ->getQuery()
            ->enableResultCache(43200)
            ->getResult()
        ;

        $committees = [];

        foreach ($rows as $row) {
            if (!isset($committees[$row['id']])) {
                $committees[$row['id']] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'animator' => [
                        'id' => $row['animatorId'],
                        'firstName' => $row['firstName'],
                        'lastName' => $row['lastName'],
                        'emailAddress' => $row['emailAddress'],
                    ],
                    'membersCount' => $row['membersCount'],
                    'sympathizersCount' => $row['sympathizersCount'],
                    'features' => [],
                ];
            }

            $committees[$row['id']]['features'][] = $row['shape'];
        }

        foreach ($committees as $key => &$committee) {
            if (!$committee['features']) {
                unset($committees[$key]);
                continue;
            }
            $committee['features'] = GeometryUtils::mergeWkt($committee['features'])->toJson();
        }

        return array_values($committees);
    }
}

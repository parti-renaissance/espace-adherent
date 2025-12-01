<?php

declare(strict_types=1);

namespace App\Repository;

use App\Address\Address;
use App\Address\AddressInterface;
use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionEntity;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use App\Intl\FranceCitiesBundle;
use App\Search\SearchParametersFilter;
use App\Utils\GeometryUtils;
use App\VotingPlatform\Designation\DesignationGlobalZoneEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Committee>
 */
class CommitteeRepository extends ServiceEntityRepository
{
    use NearbyTrait;
    use GeoZoneTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Committee::class);
    }

    public function findOneByUuid(UuidInterface|string $uuid): ?Committee
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
            ->getQuery();

        return $query->getOneOrNullResult();
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
            ->setParameters(new ArrayCollection([new Parameter('address', $address->getAddress()), new Parameter('postal_code', $address->getPostalCode()), new Parameter('city_name', $address->getCityName().'%'), new Parameter('country', $address->getCountry()), new Parameter('approved', Committee::APPROVED)]))
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
                c.adherents_count = t.adherents_count,
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
                ->setParameter('distance_max', $search->getRadius());
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
            ->getResult();
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
            ->setFirstResult($offset);

        return new Paginator($query);
    }

    public function createQueryBuilderForZones(array $zones, bool $withZoneParents = true): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameters(new ArrayCollection([new Parameter('status', Committee::APPROVED)]))
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('c.createdAt', 'DESC');

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

    /**
     * @return Committee[]
     */
    public function findAllWithoutStartedElection(Designation $designation, int $offset = 0, int $limit = 200): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.currentDesignation', 'd')
            ->where('(c.currentDesignation IS NULL OR d.isCanceled = true OR (d.voteEndDate IS NOT NULL AND d.voteEndDate < :date))')
            ->andWhere('c.status = :status')
            ->setParameters(new ArrayCollection([new Parameter('status', Committee::APPROVED), new Parameter('date', $designation->getCandidacyStartDate())]))
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->groupBy('c.id');

        if ($identifier = $designation->getElectionEntityIdentifier()) {
            $qb
                ->andWhere('c.uuid = :committee_uuid')
                ->setParameter('committee_uuid', $identifier);
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
                ->setParameter('fr', AddressInterface::FRANCE);
        }

        return $qb->getQuery()->getResult();
    }

    public function findCommitteeForRecentCandidate(Designation $designation, Adherent $adherent): ?Committee
    {
        return $this->createQueryBuilder('committee')
            ->innerJoin('committee.committeeElections', 'election')
            ->innerJoin('election.designation', 'designation', Join::WITH, 'designation.type = :designation_type')
            ->innerJoin('election.candidacies', 'candidacy', Join::WITH, 'candidacy.status = :candidacy_status AND candidacy.createdAt >= :candidacy_date')
            ->innerJoin('candidacy.committeeMembership', 'membership', Join::WITH, 'membership.adherent = :adherent')
            ->andWhere('committee.status = :status')
            ->setParameters(new ArrayCollection([new Parameter('designation_type', $designation->getType()), new Parameter('candidacy_status', CandidacyInterface::STATUS_CONFIRMED), new Parameter('adherent', $adherent), new Parameter('candidacy_date', new \DateTime('-3 months')), new Parameter('status', Committee::APPROVED)]))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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
            ->andWhere('committee.status = :status')
            ->setParameters(new ArrayCollection([new Parameter('designation_type', $designation->getType()), new Parameter('adherent', $adherent), new Parameter('vote_date', new \DateTime('-3 months')), new Parameter('status', Committee::APPROVED)]))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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
            ->setParameters(new ArrayCollection([new Parameter('city', Zone::CITY), new Parameter('city_child_types', [Zone::CANTON, Zone::CITY_COMMUNITY])]))
            ->getQuery()
            ->enableResultCache(43200)
            ->getResult();

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

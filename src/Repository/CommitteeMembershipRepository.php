<?php

declare(strict_types=1);

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Adherent\Tag\TagEnum;
use App\Collection\AdherentCollection;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class CommitteeMembershipRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeMembership::class);
    }

    public function findActivityMemberships(Adherent $adherent, int $page = 1, int $limit = 5): PaginatorInterface
    {
        $queryBuilder = $this
            ->createQueryBuilder('cm')
            ->innerJoin('cm.committee', 'c')
            ->where('cm.adherent = :adherent')
            ->andWhere('c.status = :approved')
            ->andWhere('c.approvedAt IS NOT NULL')
            ->setParameter('adherent', $adherent)
            ->setParameter('approved', Committee::APPROVED)
        ;

        return $this->configurePaginator(
            $queryBuilder,
            $page,
            $limit
        );
    }

    /**
     * @return CommitteeMembership[]
     */
    public function findVotingMemberships(Committee $committee): array
    {
        return $this
            ->createQueryBuilder('cm')
            ->addSelect('adherent')
            ->innerJoin('cm.adherent', 'adherent')
            ->where('cm.committee = :committee')
            ->andWhere('cm.enableVote = :true')
            ->setParameters([
                'committee' => $committee,
                'true' => true,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function countNewMembers(Committee $committee, \DateTimeInterface $from, \DateTimeInterface $to, bool $adherentRenaissance, bool $sympathizerRenaissance): int
    {
        $qb = $this->createQueryBuilder('cm')
            ->select('COUNT(DISTINCT cm.id) AS nb')
            ->andWhere('cm.committee = :committee')
        ;

        if ($adherentRenaissance ^ $sympathizerRenaissance) {
            $qb
                ->innerJoin('cm.adherent', 'adherent')
                ->andWhere('adherent.tags LIKE :adherent_tag')
                ->setParameter('adherent_tag', ($adherentRenaissance ? TagEnum::ADHERENT : TagEnum::SYMPATHISANT).'%')
            ;
        }

        return (int) $qb
            ->andWhere('cm.joinedAt >= :date_from')
            ->andWhere('cm.joinedAt < :date_to')
            ->setParameter('committee', $committee)
            ->setParameter('date_from', $from)
            ->setParameter('date_to', $to)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns the list of all members of a committee.
     *
     * @return Adherent[]|AdherentCollection
     */
    public function findMembers(Committee $committee): AdherentCollection
    {
        return $this->createAdherentCollection($this->createCommitteeMembershipsQueryBuilder($committee)->getQuery());
    }

    /**
     * Returns the list of all committee memberships of a committee.
     */
    public function findCommitteeMemberships(Committee $committee): array
    {
        return $this->createCommitteeMembershipsQueryBuilder($committee)
            ->addSelect('a')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Creates a QueryBuilder instance to fetch memberships of a committee.
     *
     * @param Committee $committee The committee for which the memberships to fetch belong
     * @param string    $alias     The custom root alias for the query
     */
    private function createCommitteeMembershipsQueryBuilder(Committee $committee, string $alias = 'cm'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->addSelect('CASE WHEN (am.quality = :supervisor AND am.provisional = :false) THEN 1 '
                .'WHEN (am.quality = :supervisor AND am.provisional = :true) THEN 2 '
                .'WHEN ('.$alias.'.privilege = :host) THEN 3 '
                .'WHEN (am.committee IS NOT NULL) THEN 4 '
                .'ELSE 5 END '
                .'AS HIDDEN score')
            ->innerJoin($alias.'.adherent', 'a')
            ->leftJoin(
                'a.adherentMandates',
                'am',
                Join::WITH,
                $alias.'.committee = am.committee AND am.finishAt IS NULL'
            )
            ->where($alias.'.committee = :committee')
            ->orderBy('score', 'ASC')
            ->setParameter('committee', $committee)
            ->setParameter('host', CommitteeMembership::COMMITTEE_HOST)
            ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
            ->setParameter('true', true)
            ->setParameter('false', false)
        ;
    }

    /**
     * Creates an AdherentCollection instance with the results of a Query.
     *
     * The query must return a list of CommitteeMembership entities.
     *
     * @param Query $query The query to execute
     */
    private function createAdherentCollection(Query $query): AdherentCollection
    {
        return new AdherentCollection(
            array_map(
                function (CommitteeMembership $membership) {
                    return $membership->getAdherent();
                },
                $query->getResult()
            )
        );
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostFirstName(string $firstName): array
    {
        return $this->findCommitteesUuidByHost([
            'firstName' => $firstName,
        ]);
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostLastName(string $lastName): array
    {
        return $this->findCommitteesUuidByHost([
            'lastName' => $lastName,
        ]);
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostEmailAddress(string $emailAddress): array
    {
        return $this->findCommitteesUuidByHost([
            'emailAddress' => $emailAddress,
        ]);
    }

    public function findCommitteesUuidByHost(array $criteria): array
    {
        $qb = $this
            ->createQueryBuilder('cm')
            ->select('c.uuid')
            ->innerJoin('cm.committee', 'c')
            ->innerJoin('cm.adherent', 'a')
            ->leftJoin('c.adherentMandates', 'am')
            ->andWhere(new Orx()
                ->add('cm.privilege = :host')
                ->add('am.quality = :supervisor AND am.finishAt IS NULL')
            )
            ->setParameters([
                'host' => CommitteeMembership::COMMITTEE_HOST,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
            ])
        ;

        if (isset($criteria['firstName'])) {
            $qb
                ->andWhere('a.firstName LIKE :firstName')
                ->setParameter('firstName', '%'.$criteria['firstName'].'%')
            ;
        }

        if (isset($criteria['lastName'])) {
            $qb
                ->andWhere('a.lastName LIKE :lastName')
                ->setParameter('lastName', '%'.$criteria['lastName'].'%')
            ;
        }

        if (isset($criteria['emailAddress'])) {
            $qb
                ->andWhere('a.emailAddress LIKE :emailAddress')
                ->setParameter('emailAddress', '%'.$criteria['emailAddress'].'%')
            ;
        }

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($qb->getQuery()->getArrayResult(), 'uuid'));
    }

    public function isAdherentInCommittee(Adherent $adherent, Committee $committee): bool
    {
        return 0 !== $this->count(['adherent' => $adherent, 'committee' => $committee]);
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getCandidacyMemberships(CommitteeElection $election): array
    {
        return $this->createQueryBuilder('m')
            ->addSelect('a', 'c')
            ->innerJoin('m.committeeCandidacies', 'c')
            ->innerJoin('m.adherent', 'a')
            ->where('c.committeeElection = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CommitteeMembership[]
     */
    public function findVotingForElectionMemberships(
        Committee $committee,
        Designation $designation,
        bool $withCertified = true,
    ): array {
        return $this->createQueryBuilderForVotingMemberships($committee, $designation, $withCertified)
            ->getQuery()
            ->getResult()
        ;
    }

    public function committeeHasVotersForElection(Committee $committee, Designation $designation): bool
    {
        return 0 < (int) $this->createQueryBuilderForVotingMemberships($committee, $designation, false)
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CommitteeMembership[]
     */
    public function findAvailableMemberships(CommitteeCandidacy $candidacy, string $query): array
    {
        $membership = $candidacy->getCommitteeMembership();
        $refDate = $candidacy->getElection()->getVoteEndDate() ?? new \DateTime();

        return $this
            ->createQueryBuilder('membership')
            ->addSelect('adherent')
            ->innerJoin('membership.adherent', 'adherent')
            ->leftJoin('membership.committeeCandidacies', 'candidacy', Join::WITH, 'candidacy.committeeMembership = membership AND candidacy.committeeElection = :election')
            ->where('membership.committee = :committee')
            ->andWhere('candidacy IS NULL OR candidacy.status = :candidacy_draft_status')
            ->andWhere('membership.id != :membership_id')
            ->andWhere('adherent.gender = :gender AND adherent.status = :adherent_status')
            ->andWhere('(adherent.firstName LIKE :query OR adherent.lastName LIKE :query)')
            ->andWhere('adherent.certifiedAt IS NOT NULL AND adherent.registeredAt <= :registration_limit_date AND membership.joinedAt <= :limit_date')
            ->setParameters([
                'query' => \sprintf('%s%%', $query),
                'candidacy_draft_status' => CandidacyInterface::STATUS_DRAFT,
                'election' => $candidacy->getElection(),
                'committee' => $membership->getCommittee(),
                'membership_id' => $membership->getId(),
                'gender' => $candidacy->isFemale() ? Genders::MALE : Genders::FEMALE,
                'adherent_status' => Adherent::ENABLED,
                'registration_limit_date' => (clone $refDate)->modify('-3 months'),
                'limit_date' => (clone $refDate)->modify('-30 days'),
            ])
            ->orderBy('adherent.lastName')
            ->addOrderBy('adherent.firstName')
            ->getQuery()
            ->getResult()
        ;
    }

    private function createQueryBuilderForVotingMemberships(Committee $committee, Designation $designation, bool $onlyCertified = true): QueryBuilder
    {
        $refDate = \DateTimeImmutable::createFromMutable($designation->getVoteEndDate());

        $qb = $this->createQueryBuilder('membership')
            ->innerJoin('membership.adherent', 'adherent')
            ->where('membership.committee = :committee')
            ->andWhere('membership.joinedAt <= :joined_at_min')
            ->andWhere('adherent.tags LIKE :adherent_tag')
            ->setParameters([
                'committee' => $committee,
                'adherent_tag' => TagEnum::ADHERENT.'%',
                'joined_at_min' => $designation->isCommitteeSupervisorType() ? $designation->getElectionCreationDate() : $refDate->modify('-30 days'),
            ])
        ;

        if ($onlyCertified) {
            $qb->andWhere('adherent.certifiedAt IS NOT NULL');
        }

        if (!$designation->isLimited()) {
            $forbiddenAdherents = array_column($this->getEntityManager()->createQueryBuilder()
                ->from(CommitteeCandidacy::class, 'candidacy')
                ->select('adherent.id as adherent_id, candidacy.id as candidacy_id')
                ->innerJoin('candidacy.committeeElection', 'election')
                ->innerJoin('candidacy.committeeMembership', 'membership')
                ->innerJoin('membership.adherent', 'adherent')
                ->innerJoin('adherent.committeeMembership', 'other_membership', Join::WITH, 'other_membership.committee = :committee')
                ->where('election.designation = :designation')
                ->andWhere('candidacy.status = :status')
                ->andWhere('election.committee != :committee')
                ->setParameters([
                    'committee' => $committee,
                    'status' => CandidacyInterface::STATUS_CONFIRMED,
                    'designation' => $designation,
                ])
                ->getQuery()
                ->getArrayResult(), 'adherent_id'
            );

            if ($forbiddenAdherents) {
                $qb
                    ->andWhere('adherent.id NOT IN (:candidate_adherents)')
                    ->setParameter('candidate_adherents', $forbiddenAdherents)
                ;
            }
        }

        return $qb;
    }

    public function findMembershipFromAdherentUuidAndCommittee(
        UuidInterface $adherentUuid,
        Committee $committee,
    ): ?CommitteeMembership {
        return $this
            ->createQueryBuilder('cm')
            ->innerJoin('cm.adherent', 'a')
            ->where('cm.committee = :committee')
            ->andWhere('a.uuid = :adherent_uuid')
            ->andWhere('a.tags LIKE :adherent_tag')
            ->setParameters([
                'adherent_uuid' => $adherentUuid,
                'committee' => $committee,
                'adherent_tag' => TagEnum::ADHERENT.'%',
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

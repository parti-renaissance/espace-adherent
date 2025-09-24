<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Address\AddressInterface;
use App\Adherent\AdherentAutocompleteFilter;
use App\Adherent\AdherentRoleEnum;
use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\AppSession\SessionStatusEnum;
use App\Collection\AdherentCollection;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\Audience\AudienceInterface;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Pap\CampaignHistory as PapCampaignHistory;
use App\Entity\Phoning\Campaign;
use App\Entity\Phoning\CampaignHistory;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VotersList;
use App\Event\Request\CountInvitationsRequest;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Membership\MembershipSourceEnum;
use App\MyTeam\RoleEnum;
use App\Pap\CampaignHistoryStatusEnum as PapCampaignHistoryStatusEnum;
use App\Phoning\CampaignHistoryStatusEnum;
use App\PublicId\PublicIdRepositoryInterface;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\AreaUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdherentRepository extends ServiceEntityRepository implements UserLoaderInterface, UserProviderInterface, PublicIdRepositoryInterface
{
    use NearbyTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }
    use PaginatorTrait;
    use GeoZoneTrait;
    use AudienceFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adherent::class);
    }

    public function countAdherents(): int
    {
        return (int) $this
            ->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.tags LIKE :adherent_tag')
            ->setParameter('adherent_tag', TagEnum::ADHERENT.'%')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Finds an Adherent instance by its email address.
     */
    public function findOneByEmail(string $email): ?Adherent
    {
        return $this->findOneBy(['emailAddress' => $email]);
    }

    public function findIdentifiersByEmail(string $email): ?array
    {
        return $this->createQueryBuilder('a')
            ->select('a.id, a.uuid, a.emailAddress')
            ->where('a.emailAddress = :email_address')
            ->setParameter('email_address', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds an active Adherent instance by its email address.
     */
    public function findOneByEmailAndStatus(string $email, array $statuses): ?Adherent
    {
        return $this->createQueryBuilder('adherent')
            ->where('adherent.emailAddress = :email')
            ->andWhere('adherent.status IN (:statuses)')
            ->setParameters([
                'email' => $email,
                'statuses' => $statuses,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Finds an active Adherent instance by its id.
     */
    public function findOneActiveById(int $id): ?Adherent
    {
        return $this->createQueryBuilder('adherent')
            ->where('adherent.id = :id')
            ->andWhere('adherent.status = :status')
            ->setParameters([
                'id' => $id,
                'status' => Adherent::ENABLED,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function loadUserByUuid(UuidInterface $uuid): ?Adherent
    {
        return $this->createQueryBuilderForAdherentWithRoles($alias = 'a')
            ->where($alias.'.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?Adherent
    {
        return $this->createQueryBuilderForAdherentWithRoles('a')
            ->addSelect('st')
            ->addSelect('sl')
            ->addSelect('tm')
            ->addSelect('ac')
            ->addSelect('slc')
            ->leftJoin('a.subscriptionTypes', 'st')
            ->leftJoin('a.staticLabels', 'sl')
            ->leftJoin('sl.category', 'slc')
            ->leftJoin('a.teamMemberships', 'tm')
            ->leftJoin('a.animatorCommittees', 'ac')
            ->andWhere('a.id = :id')
            ->setParameters(\is_array($id) ? $id : ['id' => $id])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->createQueryBuilderForAdherentWithRoles($alias = 'a')
            ->where($alias.'.emailAddress = :username')
            ->setParameter('username', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$user) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = $user::class;
        $username = $user->getUserIdentifier();

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(\sprintf('User of type "%s" and identified by "%s" is not supported by this provider.', $class, $username));
        }

        return $this->loadUserByIdentifier($username);
    }

    public function supportsClass(string $class): bool
    {
        return is_a($class, Adherent::class, true);
    }

    /**
     * @return string[]
     */
    public function findAdherentsUuidByFirstName(string $firstName): array
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->select('a.uuid')
            ->where('LOWER(a.firstName) LIKE :firstName')
            ->setParameter('firstName', '%'.strtolower($firstName).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    /**
     * @return string[]
     */
    public function findAdherentsUuidByLastName(string $lastName): array
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->select('a.uuid')
            ->where('LOWER(a.lastName) LIKE :lastName')
            ->setParameter('lastName', '%'.strtolower($lastName).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    /**
     * @return string[]
     */
    public function findAdherentsUuidByEmailAddress(string $emailAddress): array
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->select('a.uuid')
            ->where('LOWER(a.emailAddress) LIKE :emailAddress')
            ->setParameter('emailAddress', '%'.strtolower($emailAddress).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'uuid'));
    }

    public function refresh(Adherent $adherent): void
    {
        $this->getEntityManager()->refresh($adherent);
    }

    public function save(Adherent $adherent): void
    {
        $this->_em->persist($adherent);
        $this->_em->flush();
    }

    public function findOneForMatching(string $emailAddress, string $firstName, string $lastName): ?Adherent
    {
        return $this
            ->createQueryBuilder('adherent')
            ->andWhere('adherent.emailAddress = :emailAddress')
            ->andWhere('adherent.firstName = :firstName')
            ->andWhere('adherent.lastName = :lastName')
            ->setParameter('emailAddress', $emailAddress)
            ->setParameter('firstName', $firstName)
            ->setParameter('lastName', $lastName)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findIdByUuids(array $uuids): array
    {
        return array_column(
            $this->createQueryBuilder('a')
                ->select('a.id')
                ->where('a.uuid IN (:uuids)')
                ->setParameter('uuids', $uuids)
                ->getQuery()
                ->getResult(),
            'id'
        );
    }

    public function getCrmParisRecords(): array
    {
        $sql = <<<SQL
            SELECT
                a.uuid,
                a.first_name,
                a.last_name,
                a.email_address,
                a.phone,
                a.address_address AS address,
                a.address_postal_code AS postal_code,
                a.address_city_name AS city,
                IF(
                    5 = LENGTH(a.address_postal_code),
                    CAST(SUBSTRING(a.address_postal_code, 4, 2) AS UNSIGNED),
                    NULL
                ) AS district,
                a.gender,
                DATE_FORMAT(a.birthdate, '%d/%m/%Y') AS birthdate,
                a.address_latitude AS latitude,
                a.address_longitude AS longitude,
                a.interests,
                COALESCE(
                    (
                        SELECT 1
                        FROM adherent_subscription_type ast
                        INNER JOIN subscription_type st
                            ON ast.subscription_type_id = st.id
                        WHERE ast.adherent_id = a.id
                        AND st.code = :sms_mms_subscription_code
                        LIMIT 1
                    ),
                    0
                ) AS sms_mms
            FROM adherents a
            WHERE a.address_country = :country_code_france
            AND a.address_postal_code LIKE :prefix_postalcode_paris
            AND EXISTS (
                SELECT 1
                FROM adherent_subscription_type ast
                INNER JOIN subscription_type st
                    ON ast.subscription_type_id = st.id
                WHERE ast.adherent_id = a.id
                AND st.code = :candidate_email_subscription_code
            );
            SQL;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('country_code_france', AddressInterface::FRANCE);
        $stmt->bindValue('prefix_postalcode_paris', AreaUtils::PREFIX_POSTALCODE_PARIS_DISTRICTS.'%');
        $stmt->bindValue('candidate_email_subscription_code', SubscriptionTypeEnum::CANDIDATE_EMAIL);
        $stmt->bindValue('sms_mms_subscription_code', SubscriptionTypeEnum::MILITANT_ACTION_SMS);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function findDuplicateCertified(
        string $firstName,
        string $lastName,
        \DateTimeInterface $birthDate,
        Adherent $ignoredAdherent,
    ): ?Adherent {
        return $this
            ->createQueryBuilder('a')
            ->andWhere('a.firstName = :first_name')
            ->andWhere('a.lastName = :last_name')
            ->andWhere('a.birthdate = :birth_date')
            ->andWhere('a.certifiedAt IS NOT NULL')
            ->andWhere('a != :ignored_adherent')
            ->setParameters([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $birthDate,
                'ignored_adherent' => $ignoredAdherent,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findSimilarProfilesForElectedRepresentative(ElectedRepresentative $electedRepresentative)
    {
        return $this->createQueryBuilder('a')
            ->where('LOWER(a.firstName) = :firstName AND LOWER(a.lastName) = :lastName')
            ->orWhere('a.birthdate = :birthDate')
            ->setParameters([
                'firstName' => $electedRepresentative->getFirstName(),
                'lastName' => $electedRepresentative->getLastName(),
                'birthDate' => $electedRepresentative->getBirthDate(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNameByUuid(string $uuid): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.firstName', 'a.lastName')
            ->where('a.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findCommitteeSupervisors(Committee $committee): array
    {
        return $this->createCommitteeSupervisorsQueryBuilder($committee)->getQuery()->getResult();
    }

    public function countCommitteeSupervisors(Committee $committee): int
    {
        return (int) $this->createCommitteeSupervisorsQueryBuilder($committee)
            ->select('COUNT(DISTINCT a.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findCommitteeHosts(Committee $committee, bool $withoutSupervisors = false): AdherentCollection
    {
        return new AdherentCollection(
            $this->createCommitteeHostsQueryBuilder($committee, $withoutSupervisors)
                ->getQuery()
                ->getResult()
        );
    }

    public function countCommitteeHosts(Committee $committee, bool $withoutSupervisors = false): int
    {
        return (int) $this->createCommitteeHostsQueryBuilder($committee, $withoutSupervisors)
            ->select('COUNT(DISTINCT a.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns whether or not the given adherent is already an host of at least
     * one committee.
     */
    public function hostCommittee(Adherent $adherent, ?Committee $committee = null): bool
    {
        $result = (int) $this->createCommitteeHostsQueryBuilder($committee)
            ->select('COUNT(DISTINCT a.id)')
            ->andWhere('a.id = :adherent_id')
            ->setParameter('adherent_id', $adherent->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return $result > 0;
    }

    private function createCommitteeSupervisorsQueryBuilder(Committee $committee): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.adherentMandates', 'am')
            ->where('am.committee = :committee AND am.quality = :supervisor AND am.finishAt IS NULL')
            ->setParameters([
                'committee' => $committee,
                'supervisor' => CommitteeMandateQualityEnum::SUPERVISOR,
            ])
        ;
    }

    private function createCommitteeHostsQueryBuilder(
        ?Committee $committee = null,
        bool $withoutSupervisors = false,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('a');

        $cmCondition = '';
        $amCondition = '';
        if ($committee) {
            $cmCondition = 'cm.committee = :committee AND ';
            $amCondition = 'am.committee = :committee AND ';
            $qb->setParameter('committee', $committee);
        }

        if ($withoutSupervisors) {
            return $qb
                ->leftJoin(CommitteeMembership::class, 'cm', Join::WITH, $cmCondition.'cm.adherent = a')
                ->where($cmCondition.'cm.privilege = :privilege')
                ->addOrderBy('cm.privilege', 'ASC')
                ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ;
        }

        return $qb
            ->leftJoin('a.adherentMandates', 'am', Join::WITH, $amCondition.'am.adherent = a')
            ->leftJoin(CommitteeMembership::class, 'cm', Join::WITH, $cmCondition.'cm.adherent = a')
            ->where((new Orx())
                ->add('cm.privilege = :privilege')
                ->add('am.quality = :supervisor AND am.finishAt IS NULL')
            )
            ->orderBy('am.quality', 'DESC')
            ->addOrderBy('am.provisional', 'ASC')
            ->addOrderBy('cm.privilege', 'DESC')
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR)
        ;
    }

    public function createQueryBuilderForAudience(AudienceInterface $audience): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('adherent')
            ->andWhere('adherent.status = :adherent_status')
            ->setParameter('adherent_status', Adherent::ENABLED)
            ->andWhere((new Orx())
                ->add('adherent.source IS NULL')
                ->add('adherent.source = :source_jme')
                ->add('adherent.source = :source_renaissance')
            )
            ->setParameter('source_jme', MembershipSourceEnum::JEMENGAGE)
            ->setParameter('source_renaissance', MembershipSourceEnum::RENAISSANCE)
        ;

        if ($firstName = $audience->getFirstName()) {
            $qb
                ->andWhere('adherent.firstName LIKE :first_name')
                ->setParameter('first_name', $firstName.'%')
            ;
        }

        if ($lastName = $audience->getLastName()) {
            $qb
                ->andWhere('adherent.lastName LIKE :last_name')
                ->setParameter('last_name', $lastName.'%')
            ;
        }

        if ($gender = $audience->getGender()) {
            $qb
                ->andWhere('adherent.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($ageMin = $audience->getAgeMin()) {
            $now = new \DateTimeImmutable();
            $qb
                ->andWhere('adherent.birthdate <= :min_age_birth_date')
                ->setParameter('min_age_birth_date', $now->sub(new \DateInterval(\sprintf('P%dY', $ageMin))))
            ;
        }

        if ($ageMax = $audience->getAgeMax()) {
            $now = new \DateTimeImmutable();
            $qb
                ->andWhere('adherent.birthdate >= :max_age_birth_date')
                ->setParameter('max_age_birth_date', $now->sub(new \DateInterval(\sprintf('P%dY', $ageMax))))
            ;
        }

        if ($registeredSince = $audience->getRegisteredSince()) {
            $qb
                ->andWhere('adherent.registeredAt >= :registered_since')
                ->setParameter('registered_since', $registeredSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($registeredUntil = $audience->getRegisteredUntil()) {
            $qb
                ->andWhere('adherent.registeredAt <= :registered_until')
                ->setParameter('registered_until', $registeredUntil->format('Y-m-d 23:59:59'))
            ;
        }

        if (null !== $isCertified = $audience->getIsCertified()) {
            $qb->andWhere('adherent.certifiedAt '.($isCertified ? 'IS NOT NULL' : 'IS NULL'));
        }

        if ($zones = $audience->getZones()->toArray()) {
            $this->withGeoZones(
                $zones,
                $qb,
                'adherent',
                Adherent::class,
                'a2',
                'zones',
                'z2',
                function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                    $zoneQueryBuilder
                        ->andWhere(\sprintf('%s.status = :adherent_status', $entityClassAlias))
                        ->andWhere((new Orx())
                            ->add(\sprintf('%s.source IS NULL', $entityClassAlias))
                            ->add(\sprintf('%s.source = :source_jme', $entityClassAlias))
                            ->add(\sprintf('%s.source = :source_renaissance', $entityClassAlias))
                        )
                    ;
                }
            );
        }

        if ($audience->getRoles()) {
            $this->withAdherentRole($qb, 'adherent', $audience->getRoles());
        }

        if (null !== $hasSmsSubscription = $audience->getHasSmsSubscription()) {
            $qb
                ->leftJoin('adherent.subscriptionTypes', 'subscription_type', Join::WITH, 'subscription_type.code = :sms_subscription_code')
                ->setParameter('sms_subscription_code', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
                ->andWhere('subscription_type.id '.($hasSmsSubscription ? 'IS NOT NULL' : 'IS NULL'))
            ;
        }

        if (null !== $isCommitteeMember = $audience->getIsCommitteeMember()) {
            $qb
                ->leftJoin('adherent.committeeMembership', 'committee_membership')
                ->andWhere('committee_membership.id '.($isCommitteeMember ? 'IS NOT NULL' : 'IS NULL'))
            ;
        }

        return $qb;
    }

    public function findOneToCall(Campaign $campaign, Adherent $excludedAdherent): ?Adherent
    {
        $adherents = iterator_to_array($this->findForPhoningCampaign($campaign, $excludedAdherent));

        return $adherents ? $adherents[array_rand($adherents)] : null;
    }

    public function findForPhoningCampaign(
        Campaign $campaign,
        ?Adherent $excludedAdherent = null,
        int $limit = 10,
    ): PaginatorInterface {
        $queryBuilder = $this->createQueryBuilderForAudience($campaign->getAudience())
            ->select('adherent')
            ->andWhere('adherent.phone LIKE :fr_phone')
            ->andWhere(\sprintf('adherent.id NOT IN (%s)',
                $this->createQueryBuilder('a3')
                    ->select('DISTINCT(a3.id)')
                    ->innerJoin(CampaignHistory::class, 'ch3', Join::WITH, 'ch3.adherent = a3')
                    ->andWhere('ch3.status = :completed')
                    ->andWhere('ch3.campaign = :campaign')
            ))
            ->andWhere(\sprintf('adherent.id NOT IN (%s)',
                $this->createQueryBuilder('a4')
                    ->select('DISTINCT(a4.id)')
                    ->innerJoin(CampaignHistory::class, 'ch4', Join::WITH, 'ch4.adherent = a4')
                    ->andWhere((new Orx())
                        ->add('ch4.status IN (:not_callable)')
                        ->add('ch4.status = :dont_remind AND ch4.campaign = :campaign')
                    )
            ))
            ->andWhere(\sprintf('adherent.id NOT IN (%s)',
                $this->createQueryBuilder('a5')
                    ->select('DISTINCT(a5.id)')
                    ->innerJoin(CampaignHistory::class, 'ch5', Join::WITH, 'ch5.adherent = a5')
                    ->andWhere('ch5.status IN (:recall)')
                    ->andWhere('DATE(ch5.beginAt) = CURRENT_DATE()')
            ))
            ->setParameter('fr_phone', '+33%')
            ->setParameter('not_callable', CampaignHistoryStatusEnum::NOT_CALLABLE)
            ->setParameter('recall', CampaignHistoryStatusEnum::CALLABLE_LATER + [CampaignHistoryStatusEnum::COMPLETED])
            ->setParameter('completed', CampaignHistoryStatusEnum::COMPLETED)
            ->setParameter('dont_remind', CampaignHistoryStatusEnum::INTERRUPTED_DONT_REMIND)
            ->setParameter('campaign', $campaign)
        ;

        if ($excludedAdherent) {
            $queryBuilder
                ->andWhere('adherent != :excluded_adherent')
                ->setParameter('excluded_adherent', $excludedAdherent)
            ;
        }

        return $this->configurePaginator($queryBuilder, 1, $limit);
    }

    public function findScoresByCampaign(Campaign $campaign): array
    {
        return $this->createQueryBuilder('adherent')
            ->select('adherent.id, adherent.firstName')
            ->addSelect('COUNT(campaignHistory.id) AS nb_calls')
            ->addSelect('COUNT(campaignHistory.dataSurvey) as nb_surveys')
            ->innerJoin('adherent.teamMemberships', 'teamMemberships')
            ->innerJoin('teamMemberships.team', 'team')
            ->innerJoin(Campaign::class, 'campaign', Join::WITH, 'campaign.team = team')
            ->leftJoin(
                'campaign.campaignHistories',
                'campaignHistory',
                Join::WITH,
                'campaignHistory.caller = adherent AND campaignHistory.status != :send'
            )
            ->where('campaign = :campaign')
            ->groupBy('adherent.id')
            ->orderBy('nb_surveys', 'DESC')
            ->addOrderBy('campaignHistory.beginAt', 'DESC')
            ->addOrderBy('adherent.id', 'ASC')
            ->setParameters([
                'campaign' => $campaign,
                'send' => CampaignHistoryStatusEnum::SEND,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllAdherentsForLocalElection(
        array $electionZones,
        ?\DateTime $registerDeadline = null,
        ?\DateTime $membershipDeadline = null,
        ?int $membershipFromYear = null,
    ): array {
        if (!$electionZones) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('a')
            ->select('PARTIAL a.{id, emailAddress, firstName, lastName}')
            ->where('a.status = :status')
            ->andWhere('a.tags LIKE :adherent_tag')
            ->setParameters([
                'status' => Adherent::ENABLED,
                'adherent_tag' => TagEnum::ADHERENT.'%',
            ])
        ;

        $this->withGeoZones(
            $electionZones,
            $queryBuilder,
            'a',
            Adherent::class,
            'a2',
            'zones',
            'z2'
        );

        if ($registerDeadline) {
            $queryBuilder
                ->andWhere('a.registeredAt <= :date')
                ->setParameter('date', $registerDeadline)
            ;
        }

        if ($membershipDeadline) {
            $queryBuilder
                ->andWhere('a.lastMembershipDonation <= :last_membership_donation')
                ->setParameter('last_membership_donation', $membershipDeadline)
            ;
        }

        if ($membershipFromYear) {
            $condition = new Orx();

            foreach (range($membershipFromYear, date('Y')) as $key => $year) {
                $condition->add('a.tags LIKE :tag'.$key);
                $queryBuilder->setParameter('tag'.$key, TagEnum::getAdherentYearTag($year).'%');
            }

            $queryBuilder->andWhere($condition);
        }

        return $queryBuilder->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)->getResult();
    }

    public function findFullScoresByCampaign(Campaign $campaign, bool $apiContext = false): array
    {
        $qb = $this->createQueryBuilder('adherent')
            ->select('adherent.firstName, adherent.lastName')
            ->addSelect('COUNT(campaignHistory.id) AS nb_calls')
            ->addSelect('COUNT(campaignHistory.dataSurvey) as nb_surveys')
            ->addSelect('SUM(IF(campaignHistory.status = :completed, 1, 0)) as nb_completed')
            ->addSelect('SUM(IF(campaignHistory.status = :to_unsubscribe, 1, 0)) as nb_to_unsubscribe')
            ->addSelect('SUM(IF(campaignHistory.status = :to_unjoin, 1, 0)) as nb_to_unjoin')
            ->addSelect('SUM(IF(campaignHistory.status = :to_remind, 1, 0)) as nb_to_remind')
            ->addSelect('SUM(IF(campaignHistory.status = :not_respond, 1, 0)) as nb_not_respond')
            ->addSelect('SUM(IF(campaignHistory.status = :failed, 1, 0)) as nb_failed')
            ->innerJoin('adherent.teamMemberships', 'teamMemberships')
            ->innerJoin('teamMemberships.team', 'team')
            ->innerJoin(Campaign::class, 'campaign', Join::WITH, 'campaign.team = team')
            ->leftJoin(
                'campaign.campaignHistories',
                'campaignHistory',
                Join::WITH,
                'campaignHistory.caller = adherent AND campaignHistory.status != :send'
            )
            ->where('campaign = :campaign')
            ->groupBy('adherent.id')
            ->orderBy('nb_surveys', 'DESC')
            ->addOrderBy('campaignHistory.beginAt', 'DESC')
            ->setParameters([
                'campaign' => $campaign,
                'send' => CampaignHistoryStatusEnum::SEND,
                'completed' => CampaignHistoryStatusEnum::COMPLETED,
                'to_unsubscribe' => CampaignHistoryStatusEnum::TO_UNSUBSCRIBE,
                'to_unjoin' => CampaignHistoryStatusEnum::TO_UNJOIN,
                'to_remind' => CampaignHistoryStatusEnum::TO_REMIND,
                'not_respond' => CampaignHistoryStatusEnum::NOT_RESPOND,
                'failed' => CampaignHistoryStatusEnum::FAILED,
            ])
        ;

        if (!$apiContext) {
            $qb
                ->addSelect('adherent.id')
                ->addOrderBy('adherent.id', 'ASC')
            ;
        }

        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findFullScoresByPapCampaign(
        PapCampaign $campaign,
        int $page = 1,
        int $limit = 100,
    ): PaginatorInterface {
        $qb = $this->createQueryBuilder('adherent')
            ->select('adherent.firstName AS first_name, adherent.lastName AS last_name')
            ->addSelect('COUNT(campaignHistory.id) AS nb_visited_doors')
            ->addSelect('COUNT(campaignHistory.dataSurvey) AS nb_surveys')
            ->addSelect('SUM(IF(campaignHistory.status = :accept_to_answer, 1, 0)) AS nb_accept_to_answer')
            ->addSelect('SUM(IF(campaignHistory.status = :dont_accept_to_answer, 1, 0)) AS nb_dont_accept_to_answer')
            ->addSelect('SUM(IF(campaignHistory.status = :contact_later, 1, 0)) AS nb_contact_later')
            ->addSelect('SUM(IF(campaignHistory.status = :door_open, 1, 0)) AS nb_door_open')
            ->addSelect('SUM(IF(campaignHistory.status = :door_closed, 1, 0)) AS door_closed')
            ->innerJoin(PapCampaignHistory::class, 'campaignHistory', Join::WITH, 'campaignHistory.questioner = adherent')
            ->where('campaignHistory.campaign = :campaign')
            ->groupBy('adherent.id')
            ->orderBy('nb_surveys', 'DESC')
            ->setParameters([
                'campaign' => $campaign,
                'accept_to_answer' => PapCampaignHistoryStatusEnum::ACCEPT_TO_ANSWER,
                'dont_accept_to_answer' => PapCampaignHistoryStatusEnum::DONT_ACCEPT_TO_ANSWER,
                'contact_later' => PapCampaignHistoryStatusEnum::CONTACT_LATER,
                'door_open' => PapCampaignHistoryStatusEnum::DOOR_OPEN,
                'door_closed' => PapCampaignHistoryStatusEnum::DOOR_CLOSED,
            ])
        ;

        return $this->configurePaginator($qb, $page, $limit, null, false);
    }

    /**
     * @return Adherent[]
     */
    public function findAdherentByAutocompletion(AdherentAutocompleteFilter $filter, int $limit = 10): array
    {
        $search = trim($filter->q);

        if (!$search) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', Adherent::ENABLED)
        ;

        if (filter_var($search, \FILTER_VALIDATE_EMAIL)) {
            $qb
                ->andWhere('a.emailAddress = :email')
                ->setParameter('email', $search)
            ;
        } else {
            $qb
                ->andWhere('CONCAT(a.firstName, \' \', a.lastName, \' \', a.emailAddress) LIKE :name OR a.publicId = :public_id')
                ->setParameter('name', '%'.$search.'%')
                ->setParameter('public_id', $search)
            ;
        }

        if ($filter->committee || $filter->managedCommitteeUuids) {
            $qb
                ->innerJoin('a.committeeMembership', 'membership')
                ->innerJoin('membership.committee', 'committee')
                ->andWhere('a.tags like :adherent_tag')
                ->setParameter('adherent_tag', TagEnum::ADHERENT.'%')
            ;

            if ($filter->committee) {
                $qb
                    ->andWhere('committee = :committee')
                    ->setParameter('committee', $filter->committee)
                ;
            } else {
                $qb
                    ->andWhere('committee.uuid IN (:committees)')
                    ->setParameter('committees', $filter->managedCommitteeUuids)
                ;
            }
        }

        if ($filter->tag) {
            $qb
                ->andWhere('a.tags LIKE :adherent_tag')
                ->setParameter('adherent_tag', $filter->tag.'%')
            ;
        }

        return $qb->setMaxResults($limit)->getQuery()->getResult();
    }

    public function countInZones(array $zones, bool $adherentRenaissance, bool $sympathizerRenaissance, ?int $since = null): int
    {
        if (!$zones) {
            return 0;
        }

        $queryBuilder = $this
            ->createQueryBuilderForZones($zones, $adherentRenaissance, $sympathizerRenaissance)
            ->select('COUNT(DISTINCT adherent.id)')
        ;

        if ($since >= 2022 && $since <= date('Y')) {
            $yearCondition = new Orx();
            foreach (range($since, date('Y')) as $year) {
                $yearCondition->add('adherent.tags LIKE :adherent_tag_'.$year);
                $queryBuilder->setParameter('adherent_tag_'.$year, TagEnum::getAdherentYearTag($year).'%');
            }

            if ($yearCondition->count() > 0) {
                $queryBuilder->andWhere($yearCondition);
            }
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getAllInZones(array $zones, bool $adherentRenaissance, bool $sympathizerRenaissance, ?int $offset = null, ?int $limit = null): array
    {
        if (!$zones) {
            return [];
        }

        $qb = $this
            ->createQueryBuilderForZones($zones, $adherentRenaissance, $sympathizerRenaissance)
            ->select('PARTIAL adherent.{id, uuid, emailAddress, source, firstName, lastName, lastMembershipDonation}')
        ;

        if (null !== $offset && null !== $limit) {
            $qb
                ->setMaxResults($limit)
                ->setFirstResult($offset)
            ;
        }

        return $qb
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    public function getAllInZonesAndNotVoted(Election $election, array $zones, ?int $offset = null, ?int $limit = null): array
    {
        if (!$zones) {
            return [];
        }

        $qb = $this
            ->createQueryBuilderForZones($zones, true, false)
            ->select('PARTIAL adherent.{id, uuid, emailAddress, source, firstName, lastName, lastMembershipDonation}')
            ->leftJoin(Voter::class, 'voter', Join::WITH, 'voter.adherent = adherent')
            ->leftJoin('voter.votersLists', 'voters_lists', Join::WITH, 'voters_lists.election = :election')
            ->leftJoin(ElectionRound::class, 'election_round', Join::WITH, 'election_round.election = :election')
            ->leftJoin(Vote::class, 'vote', Join::WITH, 'vote.voter = voter AND vote.electionRound = election_round')
            ->andWhere('vote.id IS NULL')
            ->groupBy('adherent.id')
            ->setParameter('election', $election)
        ;

        if (null !== $offset && null !== $limit) {
            $qb
                ->setMaxResults($limit)
                ->setFirstResult($offset)
            ;
        }

        return $qb
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    public function countNewAdherents(
        array $zones,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        bool $adherentRenaissance,
        bool $sympathizerRenaissance,
    ): int {
        return $this
            ->createQueryBuilderForZones($zones, $adherentRenaissance, $sympathizerRenaissance)
            ->select('COUNT(DISTINCT adherent.id) AS nb')
            ->andWhere('adherent.registeredAt >= :from_date')
            ->andWhere('adherent.registeredAt < :to_date')
            ->setParameter('from_date', $from)
            ->setParameter('to_date', $to)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createQueryBuilderForZones(
        array $zones,
        bool $adherentRenaissance,
        bool $sympathizerRenaissance,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('adherent')
            ->andWhere('adherent.status = :status')
            ->setParameters([
                'status' => Adherent::ENABLED,
            ])
        ;

        if ($adherentRenaissance ^ $sympathizerRenaissance) {
            $qb
                ->andWhere('adherent.tags like :adherent_tag')
                ->setParameter('adherent_tag', ($adherentRenaissance ? TagEnum::ADHERENT : TagEnum::SYMPATHISANT).'%')
            ;
        }

        $this->withGeoZones(
            $zones,
            $qb,
            'adherent',
            Adherent::class,
            'a2',
            'zones',
            'z2'
        );

        return $qb;
    }

    private function withAdherentRole(QueryBuilder $qb, string $alias, array $roles): void
    {
        $where = $qb->expr()->orX();

        if ($committeeMandates = array_intersect([AdherentRoleEnum::COMMITTEE_SUPERVISOR, AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR], $roles)) {
            $qb->leftJoin(\sprintf('%s.adherentMandates', $alias), 'am');
            $condition = '';

            if (1 === \count($committeeMandates)) {
                $condition = ' AND am.provisional = :provisional';
                $qb->setParameter('provisional', \in_array(AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR, $committeeMandates, true));
            }

            $where->add('am.quality = :supervisor AND am.committee IS NOT NULL AND am.finishAt IS NULL'.$condition);
            $qb->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR);
        }

        $qb->andWhere($where);
    }

    private function createQueryBuilderForAdherentWithRoles(string $alias): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->addSelect('cm')
            ->addSelect('c')
            ->addSelect('jma')
            ->addSelect('rda')
            ->addSelect('mandates')
            ->addSelect('zone_based_role')
            ->addSelect('az')
            ->leftJoin($alias.'.jecouteManagedArea', 'jma')
            ->leftJoin($alias.'.committeeMembership', 'cm')
            ->leftJoin('cm.committee', 'c')
            ->leftJoin($alias.'.receivedDelegatedAccesses', 'rda')
            ->leftJoin($alias.'.adherentMandates', 'mandates')
            ->leftJoin($alias.'.zoneBasedRoles', 'zone_based_role')
            ->leftJoin($alias.'.zones', 'az')
        ;
    }

    public function findZoneManager(Zone $zone, string $roleType, bool $withHidden = false): ?Adherent
    {
        $qb = $this->createQueryBuilder('adherent')
            ->innerJoin('adherent.zoneBasedRoles', 'zone_based_role')
            ->andWhere('zone_based_role.type = :role_type')
            ->andWhere(':zone MEMBER OF zone_based_role.zones')
            ->setParameters([
                'role_type' => $roleType,
                'zone' => $zone,
            ])
        ;

        if (!$withHidden) {
            $qb
                ->andWhere('zone_based_role.hidden = :hidden')
                ->setParameter('hidden', false)
            ;
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findElectedRepresentativeManagersForDepartmentCodes(array $departmentCodes): array
    {
        return $this
            ->createQueryBuilder('adherent')
            ->select([
                'zone.code AS department_code',
                'adherent.emailAddress AS pad_email',
                'delegated.emailAddress AS member_email',
            ])
            ->innerJoin(
                'adherent.zoneBasedRoles',
                'zone_based_role',
                Join::WITH,
                'zone_based_role.type = :type_pad'
            )
            ->innerJoin(
                'zone_based_role.zones',
                'zone',
                Join::WITH,
                'zone.code IN (:department_codes)'
            )
            ->leftJoin(
                DelegatedAccess::class,
                'rda',
                Join::WITH,
                'rda.delegator = adherent AND rda.type = :type_pad AND FIND_IN_SET(:feature_elected_representative, rda.scopeFeatures) > 0'
            )
            ->leftJoin('rda.delegated', 'delegated')
            ->setParameters([
                'department_codes' => $departmentCodes,
                'type_pad' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'feature_elected_representative' => FeatureEnum::ELECTED_REPRESENTATIVE,
            ])
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function associateWithVoterList(Designation $designation, VotersList $list): void
    {
        /** @var Connection $connection */
        $connection = $this->getEntityManager()->getConnection();

        // 1. Create voters
        $sql = <<<SQL
            INSERT IGNORE INTO voting_platform_voter (adherent_id, created_at, is_poll_voter)
            SELECT adherent.id, NOW(), 1 FROM adherents AS adherent
            WHERE
                adherent.status = :status
                AND adherent.registered_at < :since_date
                AND adherent.certified_at IS NOT NUll
                AND adherent.source IS NULL
            ON DUPLICATE KEY UPDATE is_poll_voter = 1
            SQL;
        $connection->prepare($sql)->executeStatement([
            'status' => Adherent::ENABLED,
            'since_date' => (clone $designation->getVoteStartDate())->modify('-3 months')->format(\DateTimeInterface::ATOM),
        ]);

        // 2. Associate voters with voters list
        $sql = <<<SQL
            INSERT IGNORE INTO voting_platform_voters_list_voter (voters_list_id, voter_id)
            SELECT :voter_list_id, voter.id FROM voting_platform_voter AS voter
            INNER JOIN adherents AS adherent ON adherent.id = voter.adherent_id
            WHERE
                voter.is_poll_voter = 1
                AND adherent.status = :status
                AND adherent.registered_at < :since_date
                AND adherent.certified_at IS NOT NUll
                AND adherent.source IS NULL
            SQL;

        $connection->prepare($sql)->executeStatement([
            'voter_list_id' => $list->getId(),
            'status' => Adherent::ENABLED,
            'since_date' => (clone $designation->getVoteStartDate())->modify('-3 months')->format(\DateTimeInterface::ATOM),
        ]);
    }

    /**
     * @return Adherent[]
     */
    public function findAllForCommitteeZone(Zone $zone): array
    {
        $qb = $this
            ->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, uuid, emailAddress, source, lastMembershipDonation}')
            ->addSelect('committee_membership')
            ->addSelect('cl')
            ->leftJoin('adherent.committeeMembership', 'committee_membership')
            ->leftJoin('committee_membership.committee', 'cl')
            ->andWhere('adherent.status = :enabled')
            ->setParameters([
                'enabled' => Adherent::ENABLED,
                'manual_trigger' => CommitteeMembershipTriggerEnum::MANUAL,
            ])
        ;

        $this->withGeoZones(
            [$zone],
            $qb,
            'adherent',
            Adherent::class,
            'a2',
            'zones',
            'z2',
            null,
            $zone->isCityCommunity() || $zone->isBoroughCity(),
            'zone_parent2'
        );

        $excludedAdherentQueryBuilder = $this
            ->createQueryBuilder('exl_adh')
            ->select('DISTINCT exl_adh.id')
            ->innerJoin('exl_adh.committeeMembership', 'exl_membership', Join::WITH, 'exl_membership.trigger = :manual_trigger')
            ->innerJoin('exl_membership.committee', 'committee')
        ;

        $qb
            ->andWhere(\sprintf('adherent.id NOT IN (%s)', $excludedAdherentQueryBuilder->getDQL()))
            ->setParameters(new ArrayCollection(array_merge($qb->getParameters()->toArray(), $excludedAdherentQueryBuilder->getParameters()->toArray())))
        ;

        return $qb->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)->getResult();
    }

    public function findAllWithActifLocalMandates(): array
    {
        return $this->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, emailAddress, firstName, lastName}')
            ->innerJoin(ElectedRepresentativeAdherentMandate::class, 'mandate', Join::WITH, 'mandate.adherent = adherent')
            ->where('mandate.finishAt IS NULL AND mandate.mandateType IN (:types)')
            ->andWhere('adherent.tags LIKE :adherent_tag')
            ->andWhere('adherent.status = :status')
            ->setParameters([
                'types' => MandateTypeEnum::LOCAL_TYPES,
                'adherent_tag' => TagEnum::ADHERENT.'%',
                'status' => Adherent::ENABLED,
            ])
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    public function findAllForCongressCNElection(bool $votersOnly = true, ?int $offset = null, ?int $limit = null): array
    {
        $subQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT a.id')
            ->from(Donator::class, 'donator')
            ->innerJoin('donator.donations', 'donation', Join::WITH, 'donation.donatedAt < :date AND donation.membership = 1 AND donation.status = :donation_status')
            ->innerJoin('donator.adherent', 'a')
            ->where('a.status = :status')
        ;

        $qb = $this->createQueryBuilder('adherent')
            ->select('PARTIAL adherent.{id, emailAddress, firstName, lastName}')
            ->andWhere(\sprintf('adherent.id IN (%s)', $subQuery->getDQL()))
            ->setParameters([
                'status' => Adherent::ENABLED,
                'date' => '2024-11-05 00:00:00',
                'donation_status' => Donation::STATUS_FINISHED,
            ])
        ;

        if (null !== $offset && null !== $limit) {
            $qb
                ->orderBy('adherent.id')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
            ;
        }

        if ($votersOnly) {
            $qb
                ->andWhere('adherent.tags LIKE :adherent_tag')
                ->setParameter('adherent_tag', TagEnum::getAdherentYearTag().'%')
            ;
        }

        return $qb
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * @param Zone[] $zones
     */
    public function getStatsPerZones(array $zones): array
    {
        $currentYear = date('Y');

        $baseQueryBuilder = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT IF(a.tags LIKE :tag_sympathisant, a.id, NULL)) AS total_sympathisant')
            ->addSelect('COUNT(DISTINCT IF(a.tags LIKE :tag_a_jour_n3, a.id, NULL)) AS total_year_n3')
            ->addSelect('COUNT(DISTINCT IF(a.tags LIKE :tag_a_jour_n2, a.id, NULL)) AS total_year_n2')
            ->addSelect('COUNT(DISTINCT IF(a.tags LIKE :tag_a_jour_n1, a.id, NULL)) AS total_year_n1')
            ->addSelect('COUNT(DISTINCT IF(a.tags LIKE :tag_a_jour_n, a.id, NULL)) AS total_year_n')
            ->addSelect('COUNT(DISTINCT IF(a.tags LIKE :tag_primo, a.id, NULL)) AS total_primo')
            ->addSelect('COUNT(DISTINCT IF(a.tags LIKE :tag_recotisation, a.id, NULL)) AS total_recotisation')
            ->addSelect('COUNT(DISTINCT IF(a.tags LIKE :tag_elu, a.id, NULL)) AS total_elu')
            ->where('a.status = :enabled')
            ->setParameters([
                'enabled' => Adherent::ENABLED,
                'tag_primo' => \sprintf(TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN, $currentYear).'%',
                'tag_recotisation' => \sprintf(TagEnum::ADHERENT_YEAR_RECOTISATION_TAG_PATTERN, $currentYear).'%',
                'tag_elu' => \sprintf(TagEnum::ADHERENT_YEAR_ELU_TAG_PATTERN, $currentYear).'%',
                'tag_a_jour_n' => TagEnum::getAdherentYearTag().'%',
                'tag_a_jour_n1' => TagEnum::getAdherentYearTag($currentYear - 1).'%',
                'tag_a_jour_n2' => TagEnum::getAdherentYearTag($currentYear - 2).'%',
                'tag_a_jour_n3' => TagEnum::getAdherentYearTag($currentYear - 3).'%',
                'tag_sympathisant' => TagEnum::SYMPATHISANT.'%',
            ])
        ;

        $zoneQueryBuilder = (clone $baseQueryBuilder)
            ->innerJoin('a.zones', 'z')
            ->innerJoin('z.parents', 'p')
            ->andWhere('p.id = :zone_id')
        ;
        $results = [];
        foreach ($zones as $zone) {
            $row = [
                'region' => ($region = current($zone->getParentsOfType(Zone::REGION))) ? $region->getName() : null,
                'code' => $zone->getCode(),
                'department' => $zone->getName(),
            ];

            $row = array_merge($row, $zoneQueryBuilder
                ->setParameter('zone_id', $zone->getId())
                ->getQuery()
                ->getSingleResult()
            );

            $results[] = $row;
        }

        array_unshift($results, array_merge([
            'region' => 'Total',
            'code' => 'Total',
            'department' => 'Total',
        ], $baseQueryBuilder->getQuery()->getSingleResult()));

        return $results;
    }

    public function refreshDonationDates(Adherent $adherent): void
    {
        $donationDates = $this->createQueryBuilder('a')
            ->select('MAX(donation.donatedAt) AS last, MIN(donation.donatedAt) AS first')
            ->innerJoin(Donator::class, 'donator', Join::WITH, 'donator.adherent = a')
            ->innerJoin('donator.donations', 'donation', Join::WITH, 'donation.membership = 1 AND donation.status = :status')
            ->where('a = :adherent')
            ->setParameters([
                'adherent' => $adherent,
                'status' => Donation::STATUS_FINISHED,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $adherent->setFirstMembershipDonation($donationDates['first'] ? new \DateTime($donationDates['first']) : null);
        $adherent->setLastMembershipDonation($donationDates['last'] ? new \DateTime($donationDates['last']) : null);

        $this->getEntityManager()->flush();
    }

    public function findAllForConsultation(int $minYear, array $zones): array
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('PARTIAL a.{id, uuid, emailAddress, firstName, lastName}')
            ->where('a.status = :status')
            ->setParameter('status', Adherent::ENABLED)
        ;

        $condition = new Orx();

        foreach (range($minYear, date('Y')) as $key => $year) {
            $condition->add('a.tags LIKE :tag'.$key);
            $qb->setParameter('tag'.$key, TagEnum::getAdherentYearTag($year).'%');
        }

        $qb->andWhere($condition);

        $this->withGeoZones(
            $zones,
            $qb,
            'a',
            Adherent::class,
            'a2',
            'zones',
            'z2'
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Adherent[]
     */
    public function findInZones(array $zones, string $tagPattern, ?string $subscriptionTypeCode = null): array
    {
        if (!$zones) {
            return [];
        }

        $qb = $this
            ->createQueryBuilder('a')
            ->select('PARTIAL a.{id, uuid, emailAddress, firstName, lastName}')
            ->where('a.status = :status')
            ->andWhere('a.tags LIKE :tag')
            ->setParameter('status', Adherent::ENABLED)
            ->setParameter('tag', $tagPattern.'%')
        ;

        if ($subscriptionTypeCode) {
            $qb
                ->innerJoin('a.subscriptionTypes', 'subscription_type')
                ->andWhere('subscription_type.code = :subscription_type_code')
                ->setParameter('subscription_type_code', $subscriptionTypeCode)
            ;
        }

        $this->withGeoZones(
            $zones,
            $qb,
            'a',
            Adherent::class,
            'a2',
            'zones',
            'z2'
        );

        return $qb->getQuery()->getResult();
    }

    public function findInCommittee(Committee $committee, string $tag, ?string $subscriptionTypeCode = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('PARTIAL a.{id, uuid, emailAddress, firstName, lastName}')
            ->innerJoin('a.committeeMembership', 'cm')
            ->innerJoin('cm.committee', 'c')
            ->where('c = :committee')
            ->andWhere('a.status = :status')
            ->andWhere('a.tags LIKE :adherent_tag')
            ->setParameters([
                'committee' => $committee,
                'status' => Adherent::ENABLED,
                'adherent_tag' => $tag.'%',
            ])
        ;

        if ($subscriptionTypeCode) {
            $qb
                ->innerJoin('a.subscriptionTypes', 'subscription_type')
                ->andWhere('subscription_type.code = :subscription_type_code')
                ->setParameter('subscription_type_code', $subscriptionTypeCode)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findAdherentIdsWithSubscriptionTypes(array $subscriptionTypeCodes): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.subscriptionTypes', 'subscription_type')
            ->andWhere('subscription_type.code IN (:subscription_type_codes)')
            ->andWhere('a.status = :status')
            ->andWhere('a.tags LIKE :adherent_tag')
            ->setParameters([
                'status' => Adherent::ENABLED,
                'adherent_tag' => TagEnum::ADHERENT.'%',
                'subscription_type_codes' => $subscriptionTypeCodes,
            ])
            ->getQuery()
            ->getArrayResult()
        ;

        return array_column($result, 'id');
    }

    public function findByPublicId(string $publicId, bool $partial = false): ?Adherent
    {
        return $this->createQueryBuilder('a')
            ->select($partial ? 'PARTIAL a.{id, uuid, emailAddress, publicId, firstName, lastName}' : 'a')
            ->where('a.publicId = :pid')
            ->setParameter('pid', $publicId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllByIds(array $ids, bool $partial = false): array
    {
        return $this->createQueryBuilder('a')
            ->select($partial ? 'PARTIAL a.{id, uuid, emailAddress, firstName, lastName}' : 'a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countInvitations(CountInvitationsRequest $filter): int
    {
        if ($filter->isEmpty() || !$filter->hasPerimeter()) {
            return 0;
        }

        $queryBuilder = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)')
            ->where('a.status = :status')
            ->setParameter('status', Adherent::ENABLED)
        ;

        if ($filter->zones) {
            $this->withGeoZones(
                $filter->zones,
                $queryBuilder,
                'a',
                Adherent::class,
                'a2',
                'zones',
                'z2'
            );
        }

        if ($filter->agoraUuids || $filter->agora) {
            if ($filter->agoraUuids) {
                $agoraUuids = $filter->agoraUuids;
            } else {
                $agoraUuids = [$filter->agora];
            }

            $queryBuilder
                ->innerJoin('a.agoraMemberships', 'am')
                ->innerJoin('am.agora', 'agora')
                ->andWhere('agora.uuid IN(:agora_uuids)')
                ->andWhere('agora.published = 1')
                ->setParameter('agora_uuids', $agoraUuids)
            ;
        }

        if ($filter->committeeUuids) {
            $queryBuilder
                ->innerJoin('a.committeeMembership', 'cm')
                ->innerJoin('cm.committee', 'c')
                ->andWhere('c.uuid IN (:committee_uuids)')
                ->setParameter('committee_uuids', $filter->committeeUuids)
            ;
        }

        if ($filter->roles) {
            $condition = $queryBuilder->expr()->orX();

            $zoneRoles = $delegatedAccess = $agoraRoles = $committeeRoles = [];

            foreach ($filter->roles as $role) {
                if (\in_array($role, ZoneBasedRoleTypeEnum::ALL, true)) {
                    $zoneRoles[] = $role;
                } elseif (RoleEnum::isValid($role)) {
                    $delegatedAccess[] = RoleEnum::LABELS[$role] ?? $role;
                } elseif (\in_array($role, [ScopeEnum::AGORA_PRESIDENT, ScopeEnum::AGORA_GENERAL_SECRETARY], true)) {
                    $agoraRoles[] = $role;
                } elseif (ScopeEnum::ANIMATOR === $role) {
                    $committeeRoles[] = $role;
                }
            }

            $zoneRoles = array_unique($zoneRoles);
            $agoraRoles = array_unique($agoraRoles);
            $committeeRoles = array_unique($committeeRoles);
            $delegatedAccess = array_unique($delegatedAccess);

            if ($zoneRoles) {
                $condition->add('zbr IS NOT NULL');
                $queryBuilder
                    ->leftJoin('a.zoneBasedRoles', 'zbr', Join::WITH, 'zbr.hidden = 0 AND zbr.type IN (:zone_based_roles)')
                    ->setParameter('zone_based_roles', $zoneRoles)
                ;
            }

            if ($delegatedAccess) {
                $condition->add('rda IS NOT NULL');
                $queryBuilder
                    ->leftJoin('a.receivedDelegatedAccesses', 'rda', Join::WITH, 'rda.role IN (:delegated_access)')
                    ->setParameter('delegated_access', $delegatedAccess)
                ;
            }

            if ($agoraRoles) {
                foreach ($agoraRoles as $role) {
                    if (ScopeEnum::AGORA_PRESIDENT === $role) {
                        $queryBuilder->leftJoin('a.presidentOfAgoras', 'pra');
                        $condition->add('pra IS NOT NULL');
                    } elseif (ScopeEnum::AGORA_GENERAL_SECRETARY === $role) {
                        $queryBuilder->leftJoin('a.generalSecretaryOfAgoras', 'gsa');
                        $condition->add('gsa IS NOT NULL');
                    }
                }
            }

            if ($committeeRoles) {
                $condition->add('cl IS NOT NULL');
                $queryBuilder->leftJoin('a.animatorCommittees', 'cl');
            }

            if (!$zoneRoles && !$delegatedAccess && !$agoraRoles && !$committeeRoles) {
                return 0;
            }

            $queryBuilder->andWhere($condition);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countAdherentsForMessage(AdherentMessage $message, ?bool $byEmail = null, ?bool $byPush = null, bool $asUnion = false): int
    {
        $filter = $message->getFilter();

        if (!$filter instanceof AudienceFilter) {
            return 0;
        }

        $params = [
            'status_enabled' => 'ENABLED',
            'mailchimp_subscribed' => 'subscribed',
        ];
        $types = [];

        $zones = $filter->getZone() ? [$filter->getZone()] : $filter->getZones()->toArray();
        $zoneIds = array_map(static fn ($z) => $z->getId(), $zones);

        if (empty($zoneIds) && !$filter->getCommittee()) {
            return 0;
        }

        $cteParts = [];

        if (!empty($zoneIds)) {
            $cteParts[] = 'SELECT DISTINCT adherent_id FROM adherent_zone WHERE zone_id IN (:zones)';
            $params['zones'] = $zoneIds;
            $types['zones'] = ArrayParameterType::INTEGER;
        }

        $zoneParentIds = array_values(array_filter(array_map(static fn (Zone $z) => $z->isCityGrouper() ? null : $z->getId(), $zones)));

        if (!empty($zoneParentIds)) {
            $cteParts[] = 'SELECT DISTINCT az.adherent_id
               FROM adherent_zone az
               JOIN geo_zone_parent gp ON gp.child_id = az.zone_id
               WHERE gp.parent_id IN (:zones_parents)';
            $params['zones_parents'] = $zoneParentIds;
            $types['zones_parents'] = ArrayParameterType::INTEGER;
        }

        $fromJoin = [];
        $where = [];
        $with = '';
        $joinPerimeter = '';

        if (!empty($cteParts)) {
            $with = 'WITH z_adherents AS ('.implode("\nUNION\n", $cteParts).')';
            $joinPerimeter = 'JOIN z_adherents za ON za.adherent_id = a.id';
        }

        if ($filter->getCommittee() || null !== $filter->getIsCommitteeMember()) {
            $fromJoin[] = 'LEFT JOIN committees_memberships cm ON cm.adherent_id = a.id';

            if ($filter->getCommittee()) {
                $where[] = 'cm.committee_id = :committee_id';
                $params['committee_id'] = $filter->getCommittee()->getId();
                $types['committee_id'] = ParameterType::INTEGER;
            }
            if (null !== $filter->getIsCommitteeMember()) {
                $where[] = 'cm.id '.($filter->getIsCommitteeMember() ? 'IS NOT NULL' : 'IS NULL');
            }
        }

        if ($filter->getMandateType()) {
            $fromJoin[] = 'JOIN adherent_mandate am ON am.adherent_id = a.id AND am.type = :mandate_join_type AND am.mandate_type = :mandate_type';

            $params['mandate_join_type'] = 'elected_representative';
            $params['mandate_type'] = $filter->getMandateType();
        }

        $where[] = 'a.status = :status_enabled';

        if ($filter->getGender()) {
            $where[] = 'a.gender = :gender';
            $params['gender'] = $filter->getGender();
        }

        if ($filter->getAgeMin() || $filter->getAgeMax()) {
            $now = new \DateTimeImmutable();

            if ($filter->getAgeMin()) {
                $where[] = 'a.birthdate <= :min_birth_date';
                $params['min_birth_date'] = $now->sub(new \DateInterval(\sprintf('P%dY', $filter->getAgeMin())))->format('Y-m-d');
            }

            if ($filter->getAgeMax()) {
                $where[] = 'a.birthdate >= :max_birth_date';
                $params['max_birth_date'] = $now->sub(new \DateInterval(\sprintf('P%dY', $filter->getAgeMax())))->format('Y-m-d');
            }
        }

        if ($rs = $filter->getRegisteredSince()) {
            $where[] = 'a.registered_at >= :registered_since';
            $params['registered_since'] = $rs->format('Y-m-d 00:00:00');
        }
        if ($ru = $filter->getRegisteredUntil()) {
            $where[] = 'a.registered_at <= :registered_until';
            $params['registered_until'] = $ru->format('Y-m-d 23:59:59');
        }

        if ($fs = $filter->firstMembershipSince) {
            $where[] = 'a.first_membership_donation >= :first_membership_since';
            $params['first_membership_since'] = $fs->format('Y-m-d 00:00:00');
        }
        if ($fb = $filter->firstMembershipBefore) {
            $where[] = 'a.first_membership_donation <= :first_membership_before';
            $params['first_membership_before'] = $fb->format('Y-m-d 23:59:59');
        }

        if ($ls = $filter->getLastMembershipSince()) {
            $where[] = 'a.last_membership_donation >= :last_membership_since';
            $params['last_membership_since'] = $ls->format('Y-m-d 00:00:00');
        }
        if ($lb = $filter->getLastMembershipBefore()) {
            $where[] = 'a.last_membership_donation <= :last_membership_before';
            $params['last_membership_before'] = $lb->format('Y-m-d 23:59:59');
        }

        if ($tagPrefix = $filter->adherentTags) {
            $needle = ltrim($tagPrefix, '!');
            $where[] = 'a.tags '.(str_starts_with($tagPrefix, '!') ? 'NOT LIKE' : 'LIKE').' :tag_prefix';
            $params['tag_prefix'] = $needle.'%';
        }

        $idx = 0;
        foreach (array_filter([$filter->electTags, $filter->staticTags]) as $tag) {
            $needle = ltrim($tag, '!');
            $where[] = 'a.tags '.(str_starts_with($tag, '!') ? 'NOT LIKE' : 'LIKE')." :tag_contains_$idx";
            $params["tag_contains_$idx"] = '%'.$needle.'%';
            ++$idx;
        }

        $cnx = $this->getEntityManager()->getConnection();

        $stId = null;
        if (($scope = $filter->getScope()) && !empty(SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope])) {
            $stCode = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope];
            $stId = (int) $cnx->fetchOne('SELECT id FROM subscription_type WHERE code = ?', [$stCode]);
            $params['st_id'] = $stId;
            $types['st_id'] = ParameterType::INTEGER;
        }

        $branchEmailSql = null;
        $branchPushSql = null;

        if (null !== $byEmail) {
            if ($byEmail) {
                $emailConds = ['a.mailchimp_status = :mailchimp_subscribed'];
                if ($stId) {
                    $emailConds[] = 'EXISTS (
                       SELECT 1
                       FROM adherent_subscription_type ast
                       WHERE ast.adherent_id = a.id AND ast.subscription_type_id = :st_id
                    )';
                }
                $branchEmailSql = '('.implode(' AND ', $emailConds).')';
            } else {
                $emailConds = ['a.mailchimp_status <> :mailchimp_subscribed'];
                if ($stId) {
                    $emailConds[] = 'NOT EXISTS (
                       SELECT 1
                       FROM adherent_subscription_type ast
                       WHERE ast.adherent_id = a.id AND ast.subscription_type_id = :st_id
                    )';
                }
                $branchEmailSql = '('.implode(' OR ', $emailConds).')';
            }
        }

        if (null !== $byPush) {
            $branchPushSql = ($byPush ? '' : 'NOT ').'EXISTS (
                   SELECT 1
                   FROM app_session s
                   JOIN app_session_push_token_link p ON p.app_session_id = s.id AND p.unsubscribed_at IS NULL
                   WHERE s.adherent_id = a.id AND s.status = :session_status
                )';
            $params['session_status'] = SessionStatusEnum::ACTIVE->value;
        }

        $baseFrom = 'FROM adherents a'
            .(!empty($cteParts) ? "\n$joinPerimeter" : '')
            .(!empty($fromJoin) ? "\n".implode("\n", $fromJoin) : '');

        $baseWhere = $where;

        if ($asUnion && null !== $branchEmailSql && null !== $branchPushSql) {
            $sql = ($with ? "$with\n" : '')."SELECT COUNT(*) AS cnt
                FROM (
                    SELECT DISTINCT a.id
                    $baseFrom
                    WHERE ".implode(' AND ', $baseWhere)." AND $branchPushSql
                    UNION
                    SELECT DISTINCT a.id
                    $baseFrom
                    WHERE ".implode(' AND ', $baseWhere)." AND $branchEmailSql
                ) u";

            return (int) $cnx->fetchOne($sql, $params, $types);
        }

        if (null !== $branchEmailSql) {
            $baseWhere[] = $branchEmailSql;
        }

        if (null !== $branchPushSql) {
            $baseWhere[] = $branchPushSql;
        }

        $sql = ($with ? "$with\n" : '')."SELECT COUNT(DISTINCT a.id) AS cnt $baseFrom WHERE ".implode(' AND ', $baseWhere);

        return (int) $cnx->fetchOne($sql, $params, $types);
    }

    public function publicIdExists(string $publicId): bool
    {
        return $this->count(['publicId' => $publicId]) > 0;
    }

    public function findNextCleaned(): ?Adherent
    {
        return $this->createQueryBuilder('a')
            ->addSelect('CASE WHEN a.tags LIKE :adherent_tag THEN 1 ELSE 2 END AS HIDDEN score')
            ->where('a.mailchimpStatus = :status')
            ->andWhere('a.resubscribeEmailStartedAt IS NULL OR (a.resubscribeEmailStartedAt < :date AND a.resubscribeResponse IS NULL)')
            ->setParameter('status', ContactStatusEnum::CLEANED)
            ->setParameter('date', (new \DateTime())->modify('-1 day'))
            ->setParameter('adherent_tag', TagEnum::ADHERENT.'%')
            ->orderBy('score', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }
}

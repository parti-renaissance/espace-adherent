<?php

namespace AppBundle\Repository;

use AppBundle\BoardMember\BoardMemberFilter;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\EventRegistration;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Membership\AdherentEmailSubscription;
use AppBundle\Referent\ManagedAreaUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdherentRepository extends EntityRepository implements UserLoaderInterface, UserProviderInterface
{
    use NearbyTrait;

    const CITIZEN_INITIATIVE_RADIUS = 2;
    const CITIZEN_INITIATIVE_SUPERVISOR_RADIUS = 5;

    public function count(): int
    {
        return (int) $this
            ->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Finds an Adherent instance by its email address.
     *
     * @param string $email
     *
     * @return Adherent|null
     */
    public function findByEmail(string $email)
    {
        return $this->findOneBy(['emailAddress' => $email]);
    }

    /**
     * Finds an Adherent instance by its unique UUID.
     *
     * @param string $uuid
     *
     * @return Adherent|null
     */
    public function findByUuid(string $uuid)
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function loadUserByUsername($username)
    {
        $query = $this
            ->createQueryBuilder('a')
            ->addSelect('pma')
            ->addSelect('cma')
            ->addSelect('cm')
            ->addSelect('cpm')
            ->addSelect('bm')
            ->leftJoin('a.procurationManagedArea', 'pma')
            ->leftJoin('a.coordinatorManagedArea', 'cma')
            ->leftJoin('a.memberships', 'cm')
            ->leftJoin('a.citizenProjectMemberships', 'cpm')
            ->leftJoin('a.boardMember', 'bm')
            ->where('a.emailAddress = :username')
            ->andWhere('a.status = :status')
            ->setParameter('username', $username)
            ->setParameter('status', Adherent::ENABLED)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        $username = $user->getUsername();

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('User of type "%s" and identified by "%s" is not supported by this provider.', $class, $username));
        }

        if (!$user = $this->loadUserByUsername($username)) {
            throw new UsernameNotFoundException(sprintf('Unable to find Adherent user identified by "%s".', $username));
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return Adherent::class === $class;
    }

    /**
     * Returns the total number of active Adherent accounts.
     *
     * @return int
     */
    public function countActiveAdherents(): int
    {
        $query = $this
            ->createQueryBuilder('a')
            ->select('COUNT(a.uuid)')
            ->where('a.status = :status')
            ->setParameter('status', Adherent::ENABLED)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult();
    }

    /**
     * Finds the list of adherent matching the given list of UUIDs.
     *
     * @param array $uuids
     *
     * @return AdherentCollection
     */
    public function findList(array $uuids): AdherentCollection
    {
        if (!$uuids) {
            return new AdherentCollection();
        }

        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->where($qb->expr()->in('a.uuid', $uuids))
            ->getQuery()
        ;

        return new AdherentCollection($query->getResult());
    }

    /**
     * Finds the list of referents.
     *
     * @return Adherent[]
     */
    public function findReferents(): array
    {
        return $this
            ->createReferentQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function findReferent(string $identifier): ?Adherent
    {
        $qb = $this->createReferentQueryBuilder();

        if (Uuid::isValid($identifier)) {
            $qb
                ->andWhere('a.uuid = :uuid')
                ->setParameter('uuid', Uuid::fromString($identifier)->toString())
            ;
        } else {
            $qb
                ->andWhere('LOWER(a.emailAddress) = :email')
                ->setParameter('email', $identifier)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function createReferentQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.managedArea.codes IS NOT NULL')
            ->andWhere('LENGTH(a.managedArea.codes) > 0')
            ->orderBy('LOWER(a.managedArea.codes)', 'ASC')
        ;
    }

    public function findReferentsByCommittee(Committee $committee): AdherentCollection
    {
        $qb = $this
            ->createReferentQueryBuilder()
            ->andWhere('FIND_IN_SET(:code, a.managedArea.codes) > 0')
            ->setParameter('code', ManagedAreaUtils::getCodeFromCommittee($committee))
        ;

        return new AdherentCollection($qb->getQuery()->getResult());
    }

    /**
     * Finds the list of adherents managed by the given referent.
     *
     * @param Adherent $referent
     *
     * @return Adherent[]
     */
    public function findAllManagedBy(Adherent $referent): array
    {
        if (!$referent->getManagedArea()) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
            ->select('a', 'm')
            ->leftJoin('a.memberships', 'm')
            ->orderBy('a.registeredAt', 'DESC')
            ->addOrderBy('a.firstName', 'ASC')
            ->addOrderBy('a.lastName', 'ASC')
        ;

        $codesFilter = $qb->expr()->orX();

        foreach ($referent->getManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'a.postAddress.country = \'FR\'',
                        $qb->expr()->like('a.postAddress.postalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('a.postAddress.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a collection of adherents registered for a given event.
     *
     * @param BaseEvent $event
     *
     * @return AdherentCollection
     */
    public function findByEvent(BaseEvent $event): AdherentCollection
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb
            ->join(EventRegistration::class, 'er', 'WITH', 'er.adherentUuid = a.uuid')
            ->join('er.event', 'e')
            ->where('e.id = :eventId')
            ->setParameter('eventId', $event->getId())
            ->getQuery()
        ;

        return new AdherentCollection($query->getResult());
    }

    public function findNearByCitizenInitiativeInterests(CitizenInitiative $citizenInitiative): AdherentCollection
    {
        $qb = $this
            ->createNearbyQueryBuilder(new Coordinates($citizenInitiative->getLatitude(), $citizenInitiative->getLongitude()))
            ->andWhere($this->getNearbyExpression().' <= :distance_max')
            ->setParameter('distance_max', self::CITIZEN_INITIATIVE_RADIUS);

        if (false === empty($interests = $citizenInitiative->getInterests())) {
            foreach ($interests as $index => $interest) {
                $conditions[] = $qb->expr()->eq(sprintf('json_contains(n.interests, :interest_%s)', $index), 1);
                $qb->setParameter(sprintf(':interest_%s', $index), sprintf('"%s"', $interest));
            }
            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions ?? []);
            $qb->andWhere($orX);
        }

        return new AdherentCollection($qb->getQuery()->getResult());
    }

    public function findByNearCitizenProjectOrAcceptAllNotification(CitizenProject $citizenProject, int $offset = 0, bool $excludeSupervisor = true, int $radius = CitizenProjectMessageNotifier::RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN): Paginator
    {
        $qb = $this->createNearbyQueryBuilder(
                new Coordinates(
                    $citizenProject->getLatitude(),
                    $citizenProject->getLongitude()
                )
            );

        $distance = $qb->expr()->orX();
        $distance->add($this->getNearbyExpression().' <= :distance_max')
            ->add('n.citizenProjectCreationEmailSubscriptionRadius = :citizenProjectCreationEmailSubscriptionRadius');

        $qb->andWhere($distance)
            ->setParameter('distance_max', $radius)
            ->setParameter('citizenProjectCreationEmailSubscriptionRadius', AdherentEmailSubscription::DISTANCE_ALL);

        $having = $qb->expr()->orX();
        $having->add($this->getNearbyExpression().' <= n.citizenProjectCreationEmailSubscriptionRadius')
            ->add('n.citizenProjectCreationEmailSubscriptionRadius = :acceptAllNotification');
        $qb->having($having)
            ->setParameter('acceptAllNotification', AdherentEmailSubscription::DISTANCE_ALL);

        if ($excludeSupervisor) {
            $qb->andWhere('n.uuid != :uuid')
                ->setParameter('uuid', $citizenProject->getCreatedBy());
        }

        $qb->setFirstResult($offset)
            ->setMaxResults(CitizenProjectMessageNotifier::NOTIFICATION_PER_PAGE);

        return new Paginator($qb);
    }

    public function findSupervisorsNearCitizenInitiative(CitizenInitiative $citizenInitiative): AdherentCollection
    {
        $qb = $this
            ->createNearbyQueryBuilder(new Coordinates($citizenInitiative->getLatitude(), $citizenInitiative->getLongitude()))
            ->leftJoin('n.memberships', 'cm')
            ->andWhere($this->getNearbyExpression().' <= :distance_max')
            ->andWhere('cm.privilege = :supervisor')
            ->setParameter('supervisor', CommitteeMembership::COMMITTEE_SUPERVISOR)
            ->setParameter('distance_max', self::CITIZEN_INITIATIVE_SUPERVISOR_RADIUS);

        return new AdherentCollection($qb->getQuery()->getResult());
    }

    public function findSubscribersToAdherentActivity(Adherent $followed): AdherentCollection
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->leftJoin('a.activitiySubscriptions', 's')
            ->where('s.followedAdherent = :followed')
            ->andWhere('(s.unsubscribedAt IS NULL OR s.subscribedAt > s.unsubscribedAt)')
            ->setParameters([
                'followed' => $followed,
        ]);

        return new AdherentCollection($qb->getQuery()->getResult());
    }

    public function searchBoardMembers(BoardMemberFilter $filter, Adherent $excludedMember): array
    {
        return $this
            ->createBoardMemberFilterQueryBuilder($filter, $excludedMember)
            ->getQuery()
            ->getResult()
        ;
    }

    public function paginateBoardMembers(BoardMemberFilter $filter, Adherent $excludedMember): Paginator
    {
        $qb = $this
            ->createBoardMemberFilterQueryBuilder($filter, $excludedMember)
            ->setFirstResult($filter->getOffset())
            ->setMaxResults(BoardMemberFilter::PER_PAGE)
        ;

        return new Paginator($qb);
    }

    private function createBoardMemberQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('a')
            ->addSelect('bm')
            ->addSelect('ap')
            ->addSelect('ac')
            ->addSelect('cm')
            ->addSelect('bmr')
            ->innerJoin('a.boardMember', 'bm')
            ->leftJoin('a.procurationManagedArea', 'ap')
            ->leftJoin('a.coordinatorManagedArea', 'ac')
            ->leftJoin('a.memberships', 'cm')
            ->innerJoin('bm.roles', 'bmr')
        ;
    }

    private function createBoardMemberFilterQueryBuilder(BoardMemberFilter $filter, Adherent $excludedMember): QueryBuilder
    {
        $qb = $this->createBoardMemberQueryBuilder();

        $qb->andWhere('a != :member');
        $qb->setParameter('member', $excludedMember);

        if ($queryGender = $filter->getQueryGender()) {
            $qb->andWhere('a.gender = :gender');
            $qb->setParameter('gender', $queryGender);
        }

        if ($queryAgeMinimum = $filter->getQueryAgeMinimum()) {
            $dateMaximum = new \DateTime('now');
            $dateMaximum->modify('-'.$queryAgeMinimum.' years');

            $qb->andWhere('a.birthdate <= :dateMaximum');
            $qb->setParameter('dateMaximum', $dateMaximum->format('Y-m-d'));
        }

        if ($queryAgeMaximum = $filter->getQueryAgeMaximum()) {
            $dateMinimum = new \DateTime('now');
            $dateMinimum->modify('-'.$queryAgeMaximum.' years');

            $qb->andWhere('a.birthdate >= :dateMinimum');
            $qb->setParameter('dateMinimum', $dateMinimum->format('Y-m-d'));
        }

        if ($queryFirstName = $filter->getQueryFirstName()) {
            $qb->andWhere('a.firstName LIKE :firstName');
            $qb->setParameter('firstName', '%'.$queryFirstName.'%');
        }

        if ($queryLastName = $filter->getQueryLastName()) {
            $qb->andWhere('a.lastName LIKE :lastName');
            $qb->setParameter('lastName', '%'.$queryLastName.'%');
        }

        if ($queryPostalCode = $filter->getQueryPostalCode()) {
            $queryPostalCode = array_map('trim', explode(',', $queryPostalCode));

            $postalCodeExpression = $qb->expr()->orX();

            foreach ($queryPostalCode as $key => $postalCode) {
                $postalCodeExpression->add('a.postAddress.postalCode LIKE :postalCode_'.$key);
                $qb->setParameter('postalCode_'.$key, $postalCode.'%');
            }

            $qb->andWhere($postalCodeExpression);
        }

        if (count($queryAreas = $filter->getQueryAreas())) {
            $areasExpression = $qb->expr()->orX();

            foreach ($queryAreas as $key => $area) {
                $areasExpression->add('bm.area = :area_'.$key);
                $qb->setParameter('area_'.$key, $area);
            }

            $qb->andWhere($areasExpression);
        }

        if (count($queryRoles = $filter->getQueryRoles())) {
            $rolesExpression = $qb->expr()->orX();

            foreach ($queryRoles as $key => $role) {
                $rolesExpression->add('bmr.code = :board_member_role_'.$key);
                $qb->setParameter('board_member_role_'.$key, $role);
            }

            $qb->andWhere($rolesExpression);
        }

        return $qb;
    }

    public function findSavedBoardMember(BoardMember $owner): AdherentCollection
    {
        $qb = $this
            ->createBoardMemberQueryBuilder()
            ->where(':member MEMBER OF bm.owners')
            ->setParameter('member', $owner);

        return new AdherentCollection($qb->getQuery()->getResult());
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
}

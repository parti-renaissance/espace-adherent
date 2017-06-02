<?php

namespace AppBundle\Repository;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdherentRepository extends EntityRepository implements UserLoaderInterface, UserProviderInterface
{
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
            ->addSelect('cm')
            ->leftJoin('a.procurationManagedArea', 'pma')
            ->leftJoin('a.memberships', 'cm')
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

    /**
     * Finds the list of adherents managed by the given referent.
     *
     * @param Adherent $referent
     *
     * @return Adherent[]
     */
    public function findAllManagedBy(Adherent $referent): array
    {
        return $this->createManagedByQueryBuilder($referent)->getQuery()->getResult();
    }

    /**
     * Finds the list of non-followers managed by the given referent.
     *
     * @param Adherent $referent
     *
     * @return Adherent[]
     */
    public function findNonFollowersManagedBy(Adherent $referent): array
    {
        return array_filter($this->findAllManagedBy($referent), function (Adherent $adherent) {
            return 0 === $adherent->getMemberships()->count();
        });
    }

    /**
     * Finds the list of followers managed by the given referent.
     *
     * @param Adherent $referent
     *
     * @return Adherent[]
     */
    public function findFollowersManagedBy(Adherent $referent): array
    {
        return array_filter($this->findAllManagedBy($referent), function (Adherent $adherent) {
            return $adherent->getMemberships()->count() > 0;
        });
    }

    /**
     * Finds the list of hosts managed by the given referent.
     *
     * @param Adherent $referent
     *
     * @return Adherent[]
     */
    public function findHostsManagedBy(Adherent $referent): array
    {
        return array_filter($this->findAllManagedBy($referent), function (Adherent $adherent) {
            return $adherent->isHost();
        });
    }

    private function createManagedByQueryBuilder(Adherent $referent)
    {
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

        return $qb;
    }

    /**
     * Finds a collection of adherents registered for a given event.
     *
     * @param Event $event
     *
     * @return AdherentCollection
     */
    public function findByEvent(Event $event): AdherentCollection
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
}

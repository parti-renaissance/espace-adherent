<?php

namespace AppBundle\Repository;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdherentRepository extends EntityRepository implements UserLoaderInterface
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

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     */
    public function loadUserByUsername($username)
    {
        $query = $this
            ->createQueryBuilder('a')
            ->addSelect('pma')
            ->leftJoin('a.procurationManagedArea', 'pma')
            ->where('a.emailAddress = :username')
            ->andWhere('a.status = :status')
            ->setParameter('username', $username)
            ->setParameter('status', Adherent::ENABLED)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
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
        return $this->createQueryBuilder('a')
            ->where('a.managedArea.codes IS NOT NULL')
            ->andWhere('LENGTH(a.managedArea.codes) > 0')
            ->orderBy('LOWER(a.managedArea.codes)', 'ASC')
            ->getQuery()
            ->getResult();
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
            return $adherent->getMemberships()->count() === 0;
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
            foreach ($adherent->getMemberships() as $membership) {
                if ($membership->isHostMember()) {
                    return true;
                }
            }

            return false;
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
            ->where('a.id != :self')
            ->setParameter('self', $referent->getId());

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
}

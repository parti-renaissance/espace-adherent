<?php

namespace AppBundle\Repository;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Collection\CitizenProjectMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectMembership;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CitizenProjectMembershipRepository extends EntityRepository
{
    /**
     * Creates the query builder to fetch the membership relationship between
     * an adherent and a citizen project.
     *
     * @param Adherent $adherent
     * @param string   $citizenProjectUuid
     *
     * @return QueryBuilder
     */
    private function createMembershipQueryBuilder(Adherent $adherent, string $citizenProjectUuid): QueryBuilder
    {
        $citizenProjectUuid = Uuid::fromString($citizenProjectUuid);

        return $this
            ->createQueryBuilder('cpm')
            ->where('cpm.adherent = :adherent')
            ->andWhere('cpm.citizenProjectUuid = :citizenProject')
            ->setParameter('adherent', $adherent)
            ->setParameter('citizenProject', (string) $citizenProjectUuid)
        ;
    }

    public function findCitizenProjectMembershipsForAdherent(Adherent $adherent): CitizenProjectMembershipCollection
    {
        $query = $this
            ->createQueryBuilder('cpm')
            ->where('cpm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
        ;

        return new CitizenProjectMembershipCollection($query->getResult());
    }

    public function findCitizenProjectMembership(Adherent $adherent, string $citizenProjectUuid): ?CitizenProjectMembership
    {
        return $this
            ->createMembershipQueryBuilder($adherent, $citizenProjectUuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns whether or not the given adherent is already an administrator of at least
     * one citizen project.
     *
     * @param Adherent $adherent
     * @param string   $citizenProjectUuid
     *
     * @return bool
     */
    public function administrateCitizenProject(Adherent $adherent, string $citizenProjectUuid = null): bool
    {
        $qb = $this->createQueryBuilder('cpm');

        $qb
            ->select('COUNT(cpm.uuid)')
            ->where('cpm.privilege = :privilege')
            ->andWhere('cpm.adherent = :adherent')
            ->setParameters([
                'adherent' => $adherent,
                'privilege' => CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR,
            ])
        ;

        if ($citizenProjectUuid) {
            $citizenProjectUuid = Uuid::fromString($citizenProjectUuid);
            $qb
                ->andWhere('cpm.citizenProjectUuid = :citizenProject')
                ->setParameter('citizenProject', (string) $citizenProjectUuid)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult() >= 1;
    }

    public function countAdministratorMembers(string $citizenProjectUuid): int
    {
        $citizenProjectUuid = Uuid::fromString($citizenProjectUuid);

        return $this->createQueryBuilder('cpm')
            ->select('COUNT(cpm.uuid)')
            ->where('cpm.citizenProjectUuid = :citizenProject')
            ->andWhere('cpm.privilege = :privilege')
            ->setParameters([
                'citizenProject' => (string) $citizenProjectUuid,
                'privilege' => CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns the list of all administrator memberships of a citizen project.
     *
     * @param string $citizenProjectUuid
     *
     * @return AdherentCollection
     */
    public function findAdministrators(string $citizenProjectUuid): AdherentCollection
    {
        return $this->findPriviledgedMembers($citizenProjectUuid, [CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR]);
    }

    /**
     * Returns the list of all priviledged members of a citizen project.
     *
     * @param string $citizenProjectUuid The citizen project UUID
     * @param array  $privileges         An array of privilege constants (see {@link : CitizenProjectMembership}
     *
     * @return AdherentCollection
     */
    public function findPriviledgedMembers(string $citizenProjectUuid, array $privileges): AdherentCollection
    {
        $citizenProjectUuid = Uuid::fromString($citizenProjectUuid);

        $qb = $this->createQueryBuilder('cpm');

        $query = $qb
            ->select('cpm', 'adherent')
            ->leftJoin('cpm.adherent', 'adherent')
            ->where('cpm.citizenProjectUuid = :citizenProject')
            ->andWhere($qb->expr()->in('cpm.privilege', $privileges))
            ->orderBy('cpm.joinedAt', 'ASC')
            ->setParameter('citizenProject', (string) $citizenProjectUuid)
            ->getQuery()
        ;

        return $this->createAdherentCollection($query);
    }

    /**
     * Returns the list of all priviledged memberships of a citizen project.
     *
     * @param string $citizenProjectUuid The citizen project UUID
     * @param array  $privileges         An array of privilege constants (see {@link : CitizenProjectMembership}
     *
     * @return CitizenProjectMembershipCollection
     */
    private function findPriviledgedMemberships(string $citizenProjectUuid, array $privileges): CitizenProjectMembershipCollection
    {
        $citizenProjectUuid = Uuid::fromString($citizenProjectUuid);

        $qb = $this->createQueryBuilder('cpm');

        $query = $qb
            ->where('cpm.citizenProjectUuid = :citizenProject')
            ->andWhere($qb->expr()->in('cpm.privilege', $privileges))
            ->orderBy('cpm.joinedAt', 'ASC')
            ->setParameter('citizenProject', (string) $citizenProjectUuid)
            ->getQuery()
        ;

        return new CitizenProjectMembershipCollection($query->getResult());
    }

    /**
     * Returns the list of all members of a citizen project.
     *
     * @param string $citizenProjectUuid The citizen project UUID
     *
     * @return AdherentCollection
     */
    public function findMembers(string $citizenProjectUuid): AdherentCollection
    {
        return $this->createAdherentCollection($this->createCitizenProjectMembershipsQueryBuilder($citizenProjectUuid)->getQuery());
    }

    /**
     * @return string[]
     */
    public function findCitizenProjectsUuidByAdministratorFirstName(string $firstName): array
    {
        $qb = $this->createQueryBuilder('cpm');

        $query = $qb
            ->select('cpm.citizenProjectUuid')
            ->leftJoin('cpm.adherent', 'a')
            ->where('LOWER(a.firstName) LIKE :firstName')
            ->andWhere('cpm.privilege = :privilege')
            ->setParameters([
                'firstName' => '%'.strtolower($firstName).'%',
                'privilege' => CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR,
            ])
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'citizenProjectUuid'));
    }

    /**
     * @return string[]
     */
    public function findCitizenProjectsUuidByAdministratorLastName(string $lastName): array
    {
        $qb = $this->createQueryBuilder('cpm');

        $query = $qb
            ->select('cpm.citizenProjectUuid')
            ->leftJoin('cpm.adherent', 'a')
            ->where('LOWER(a.lastName) LIKE :lastName')
            ->andWhere('cpm.privilege = :privilege')
            ->setParameters([
                'lastName' => '%'.strtolower($lastName).'%',
                'privilege' => CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR,
            ])
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'citizenProjectUuid'));
    }

    /**
     * @return string[]
     */
    public function findCitizenProjectsUuidByAdministratorEmailAddress(string $emailAddress): array
    {
        $qb = $this->createQueryBuilder('cpm');

        $query = $qb
            ->select('cpm.citizenProjectUuid')
            ->leftJoin('cpm.adherent', 'a')
            ->where('LOWER(a.emailAddress) LIKE :emailAddress')
            ->andWhere('cpm.privilege = :privilege')
            ->setParameters([
                'emailAddress' => '%'.strtolower($emailAddress).'%',
                'privilege' => CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR,
            ])
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'citizenProjectUuid'));
    }

    /**
     * Creates a QueryBuilder instance to fetch memberships of a citizen project.
     *
     * @param string $citizenProjectUuid The citizen project UUID for which the memberships to fetch belong
     * @param string $alias              The custom root alias for the query
     *
     * @return QueryBuilder
     */
    private function createCitizenProjectMembershipsQueryBuilder(string $citizenProjectUuid, string $alias = 'cpm'): QueryBuilder
    {
        $citizenProjectUuid = Uuid::fromString($citizenProjectUuid);

        return $this->createQueryBuilder($alias)
            ->leftJoin($alias.'.adherent', 'a')
            ->where($alias.'.citizenProjectUuid = :citizenProject')
            ->orderBy('a.firstName', 'ASC')
            ->setParameter('citizenProject', (string) $citizenProjectUuid)
        ;
    }

    /**
     * Returns the list of all citizen project memberships of a citizen project.
     *
     * @param string $citizenProjectUuid The citizen project UUID
     *
     * @return CitizenProjectMembershipCollection
     */
    public function findCitizenProjectMemberships(string $citizenProjectUuid): CitizenProjectMembershipCollection
    {
        $query = $this
            ->createCitizenProjectMembershipsQueryBuilder($citizenProjectUuid)
            ->addSelect('a')
            ->getQuery()
        ;

        return new CitizenProjectMembershipCollection($query->getResult());
    }

    /**
     * Creates an AdherentCollection instance with the results of a Query.
     *
     * The query must return a list of CitizenProjectMembership entities.
     *
     * @param Query $query The query to execute
     *
     * @return AdherentCollection
     */
    private function createAdherentCollection(Query $query): AdherentCollection
    {
        return new AdherentCollection(
            array_map(
                function (CitizenProjectMembership $membership) {
                    return $membership->getAdherent();
                },
                $query->getResult()
            )
        );
    }
}

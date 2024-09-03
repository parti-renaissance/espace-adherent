<?php

namespace App\Repository\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Mandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MandateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mandate::class);
    }

    public function getMandatesForPoliticalFunction(int $electedRepresentativeId): array
    {
        return $this
            ->createQueryBuilder('mandate')
            ->andWhere('mandate.electedRepresentative = :elected_representative')
            ->setParameter('elected_representative', $electedRepresentativeId)
            ->orderBy('mandate.number', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTypesAndUserListDefinitionForAdherent(
        array $mandateTypes,
        string $userListDefinitionCode,
        Adherent $adherent,
    ): array {
        return $this
            ->createQueryBuilder('mandate')
            ->select('mandate', 'geoZone')
            ->leftJoin('mandate.electedRepresentative', 'electedRepresentative')
            ->leftJoin('mandate.geoZone', 'geoZone')
            ->leftJoin('electedRepresentative.userListDefinitions', 'userListDefinition')
            ->where('mandate.type IN (:types)')
            ->andWhere('mandate.isElected = 1')
            ->andWhere('mandate.onGoing = 1')
            ->andWhere('userListDefinition.code = :uldCode')
            ->andWhere('electedRepresentative.adherent = :adherent')
            ->setParameter('types', $mandateTypes)
            ->setParameter('uldCode', $userListDefinitionCode)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByFunctionAndUserListDefinitionForAdherent(
        string $functionName,
        string $userListDefinitionCode,
        Adherent $adherent,
    ): array {
        return $this
            ->createQueryBuilder('mandate')
            ->select('mandate', 'zone')
            ->leftJoin('mandate.electedRepresentative', 'electedRepresentative')
            ->leftJoin('mandate.zone', 'zone')
            ->leftJoin('mandate.politicalFunctions', 'politicalFunction')
            ->leftJoin('electedRepresentative.userListDefinitions', 'userListDefinition')
            ->where('politicalFunction.name = :name')
            ->andWhere('mandate.isElected = 1')
            ->andWhere('mandate.onGoing = 1')
            ->andWhere('userListDefinition.code = :uldCode')
            ->andWhere('electedRepresentative.adherent = :adherent')
            ->setParameter('name', $functionName)
            ->setParameter('uldCode', $userListDefinitionCode)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }
}

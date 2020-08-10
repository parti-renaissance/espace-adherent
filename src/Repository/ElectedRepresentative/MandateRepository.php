<?php

namespace App\Repository\ElectedRepresentative;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\Mandate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MandateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

    public function findByTypeAndUserListDefinitionForAdherent(
        string $mandateType,
        string $userListDefinitionCode,
        Adherent $adherent
    ): array {
        return $this
            ->createQueryBuilder('mandate')
            ->select('mandate', 'zone')
            ->leftJoin('mandate.electedRepresentative', 'electedRepresentative')
            ->leftJoin('mandate.zone', 'zone')
            ->leftJoin('electedRepresentative.userListDefinitions', 'userListDefinition')
            ->where('mandate.type = :type')
            ->andWhere('mandate.isElected = 1')
            ->andWhere('mandate.onGoing = 1')
            ->andWhere('userListDefinition.code = :uldCode')
            ->andWhere('electedRepresentative.adherent = :adherent')
            ->setParameter('type', $mandateType)
            ->setParameter('uldCode', $userListDefinitionCode)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByFunctionAndUserListDefinitionForAdherent(
        string $functionName,
        string $userListDefinitionCode,
        Adherent $adherent
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

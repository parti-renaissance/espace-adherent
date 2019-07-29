<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ElectedRepresentativesRegister;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ElectedRepresentativesRegisterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedRepresentativesRegister::class);
    }

    public function findAllNuancePolitiqueValues(): array
    {
        return $this->findAllValues('nuancePolitique');
    }

    public function findAllTypeEluValues(): array
    {
        return $this->findAllValues('typeElu');
    }

    public function findAllNomFonctionValues(): array
    {
        return $this->findAllValues('nomFonction');
    }

    private function findAllValues(string $columnName): array
    {
        $results = $this->createQueryBuilder('e')
            ->select("DISTINCT e.$columnName")
            ->getQuery()
            ->getArrayResult()
        ;

        return \array_column($results, $columnName);
    }
}

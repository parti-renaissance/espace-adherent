<?php

declare(strict_types=1);

namespace App\Repository\LocalElection;

use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\SubstituteCandidacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubstituteCandidacyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubstituteCandidacy::class);
    }

    public function findOneByCandidaciesGroupAndEmail(
        CandidaciesGroup $candidaciesGroup,
        string $email,
    ): ?SubstituteCandidacy {
        return $this->createQueryBuilder('substitute')
            ->innerJoin('substitute.candidaciesGroup', 'candidacies_group')
            ->where('substitute.email = :email AND candidacies_group = :candidacies_group')
            ->setParameters([
                'email' => $email,
                'candidacies_group' => $candidaciesGroup,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

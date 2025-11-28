<?php

declare(strict_types=1);

namespace App\Repository\LocalElection;

use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\Candidacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidacyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidacy::class);
    }

    public function findOneByCandidaciesGroupAndEmail(CandidaciesGroup $candidaciesGroup, string $email): ?Candidacy
    {
        return $this->createQueryBuilder('candidacy')
            ->innerJoin('candidacy.candidaciesGroup', 'candidacies_group')
            ->where('candidacy.email = :email AND candidacies_group = :candidacies_group')
            ->setParameters([
                'email' => $email,
                'candidacies_group' => $candidaciesGroup,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

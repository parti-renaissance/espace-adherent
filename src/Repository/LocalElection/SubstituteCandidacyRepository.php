<?php

declare(strict_types=1);

namespace App\Repository\LocalElection;

use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\SubstituteCandidacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\LocalElection\SubstituteCandidacy>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('email', $email), new Parameter('candidacies_group', $candidaciesGroup)]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

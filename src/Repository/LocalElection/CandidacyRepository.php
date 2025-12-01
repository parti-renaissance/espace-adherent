<?php

declare(strict_types=1);

namespace App\Repository\LocalElection;

use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\Candidacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\LocalElection\Candidacy>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('email', $email), new Parameter('candidacies_group', $candidaciesGroup)]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

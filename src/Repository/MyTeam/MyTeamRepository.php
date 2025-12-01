<?php

declare(strict_types=1);

namespace App\Repository\MyTeam;

use App\Entity\Adherent;
use App\Entity\MyTeam\MyTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\MyTeam\MyTeam>
 */
class MyTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyTeam::class);
    }

    public function findOneByAdherentAndScope(Adherent $adherent, string $scope): ?MyTeam
    {
        return $this->createQueryBuilder('my_team')
            ->select('my_team, owner, members')
            ->leftJoin('my_team.owner', 'owner')
            ->leftJoin('my_team.members', 'members')
            ->where('my_team.owner = :adherent AND my_team.scope = :scope')
            ->setParameters(new ArrayCollection([new Parameter('adherent', $adherent), new Parameter('scope', $scope)]))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

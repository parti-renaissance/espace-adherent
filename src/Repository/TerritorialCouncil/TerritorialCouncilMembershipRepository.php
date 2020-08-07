<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilMembershipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncilMembership::class);
    }

    /**
     * @return TerritorialCouncilMembership[]
     */
    public function findAvailableMemberships(Candidacy $candidacy): array
    {
        $membership = $candidacy->getMembership();

        return $this
            ->createQueryBuilder('membership')
            ->innerJoin('membership.qualities', 'quality')
            ->innerJoin('membership.adherent', 'adherent')
            ->where('membership.territorialCouncil = :council')
            ->andWhere('quality.name IN (:qualities)')
            ->andWhere('membership.id != :membership_id')
            ->andWhere('adherent.gender = :gender')
            ->setParameters([
                'council' => $membership->getTerritorialCouncil(),
                'qualities' => $membership->getQualityNames(),
                'membership_id' => $membership->getId(),
                'gender' => $candidacy->isMale() ? Genders::FEMALE : Genders::MALE,
            ])
            ->orderBy('adherent.lastName')
            ->addOrderBy('adherent.firstName')
            ->getQuery()
            ->getResult()
        ;
    }
}

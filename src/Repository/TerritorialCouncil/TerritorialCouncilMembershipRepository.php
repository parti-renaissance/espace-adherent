<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\UuidEntityRepositoryTrait;
use App\TerritorialCouncil\Candidacy\SearchAvailableMembershipFilter;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilMembershipRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncilMembership::class);
    }

    /**
     * @return TerritorialCouncilMembership[]
     */
    public function findAvailableMemberships(Candidacy $candidacy, SearchAvailableMembershipFilter $filter): array
    {
        $membership = $candidacy->getMembership();

        $qb = $this
            ->createQueryBuilder('membership')
            ->addSelect('adherent', 'quality')
            ->innerJoin('membership.qualities', 'quality')
            ->innerJoin('membership.adherent', 'adherent')
            ->leftJoin('membership.candidacies', 'candidacy', Join::WITH, 'candidacy.membership = membership AND candidacy.election = :election')
            ->where('membership.territorialCouncil = :council')
            ->andWhere('candidacy IS NULL OR candidacy.status = :candidacy_draft_status')
            ->andWhere('quality.name = :quality')
            ->andWhere('membership.id != :membership_id')
            ->andWhere(sprintf('membership.id NOT IN (%s)',
                $this->createQueryBuilder('t1')
                    ->select('t1.id')
                    ->innerJoin('t1.qualities', 't2')
                    ->where('t1.territorialCouncil = :council')
                    ->andWhere('t2.name IN (:qualities)')
                    ->getDQL()
            ))
            ->andWhere('adherent.gender = :gender')
            ->setParameters([
                'candidacy_draft_status' => Candidacy::STATUS_DRAFT,
                'election' => $candidacy->getElection(),
                'council' => $membership->getTerritorialCouncil(),
                'quality' => $filter->getQuality(),
                'membership_id' => $membership->getId(),
                'gender' => $candidacy->isMale() ? Genders::FEMALE : Genders::MALE,
                'qualities' => TerritorialCouncilQualityEnum::FORBIDDEN_TO_CANDIDATE,
            ])
            ->orderBy('adherent.lastName')
            ->addOrderBy('adherent.firstName')
        ;

        if ($filter->getQuery()) {
            $qb
                ->andWhere('adherent.lastName LIKE :query')
                ->setParameter('query', sprintf('%s%%', $filter->getQuery()))
            ;
        }

        return $qb->getQuery()->getResult();
    }
}

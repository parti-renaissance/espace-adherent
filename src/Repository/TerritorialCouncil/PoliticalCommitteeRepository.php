<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PoliticalCommitteeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PoliticalCommittee::class);
    }

    public function createQueryBuilderWithReferentTagsCondition(array $referentTags): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('pc')
            ->innerJoin('pc.territorialCouncil', 'tc')
            ->innerJoin('tc.referentTags', 'tag')
        ;

        if (!$referentTags) {
            return $qb->andWhere('1 = 0');
        }

        $tagCondition = 'tag IN (:tags)';

        foreach ($referentTags as $referentTag) {
            if ('75' === $referentTag->getCode()) {
                $tagCondition = "(tag IN (:tags) OR tag.name LIKE '%Paris%')";

                break;
            }
        }

        return $qb
            ->where($tagCondition)
            ->andWhere('pc.isActive = :true')
            ->andWhere('tc.isActive = :true')
            ->setParameters([
                'tags' => $referentTags,
                'true' => true,
            ])
        ;
    }
}

<?php

namespace App\Repository\Jecoute;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Repository\GeoZoneTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class NewsRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function listForZone(array $zones): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.zone IN (:zones)')
            ->setParameter('zones', $zones)
            ->addOrderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function changePinned(News $news): void
    {
        if (!$news->isPinned()) {
            return;
        }

        $qb = $this->createQueryBuilder('news')
            ->update()
            ->set('news.pinned', ':false')
            ->where('news != :news')
            ->andWhere('news.pinned = :true')
            ->andWhere('news.visibility = :visibility')
            ->setParameters([
                'true' => true,
                'false' => false,
                'news' => $news,
                'visibility' => $news->isNationalVisibility() ? ScopeVisibilityEnum::NATIONAL : ScopeVisibilityEnum::LOCAL,
            ])
        ;

        if (!$news->isNationalVisibility()) {
            $ids = array_column($this->createQueryBuilder('news')
                ->distinct()
                ->leftJoin('news.zone', 'zone')
                ->leftJoin('zone.children', 'child', Join::WITH, 'child.type = :dpt')
                ->leftJoin('zone.parents', 'parent', Join::WITH, 'parent.type = :region')
                ->where('(zone = :zone OR child = :zone)')
                ->setParameter('dpt', Zone::DEPARTMENT)
                ->setParameter('region', Zone::REGION)
                ->setParameter('zone', $news->getZone())
                ->getQuery()
                ->getArrayResult(), 'id');

            $qb
                ->andWhere('news.id IN (:ids)')
                ->setParameter('ids', $ids)
            ;
        }

        $qb->getQuery()->execute();
    }
}

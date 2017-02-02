<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommitteeFeedItem;
use Doctrine\ORM\EntityRepository;

class CommitteeFeedItemRepository extends EntityRepository
{
    public function findMostRecentFeedMessage(string $committeeUuid = null): ?CommitteeFeedItem
    {
        return $this->findMostRecentFeedItem(CommitteeFeedItem::MESSAGE, $committeeUuid);
    }

    public function findMostRecentFeedEvent(string $committeeUuid = null): ?CommitteeFeedItem
    {
        return $this->findMostRecentFeedItem(CommitteeFeedItem::EVENT, $committeeUuid);
    }

    private function findMostRecentFeedItem(string $type, string $committeeUuid = null): ?CommitteeFeedItem
    {
        $qb = $this
            ->createQueryBuilder('i')
            ->select('i, a, e')
            ->leftJoin('i.author', 'a')
            ->leftJoin('i.event', 'e')
            ->where('i.itemType = :type')
            ->orderBy('i.createdAt', 'DESC')
            ->setParameter('type', $type);

        if ($committeeUuid) {
            $qb
                ->leftJoin('i.committee', 'c')
                ->andWhere('c.uuid = :committee')
                ->setParameter('committee', $committeeUuid);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}

<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\Event;
use AppBundle\Entity\UserDocument;
use Doctrine\ORM\EntityRepository;

class UserDocumentRepository extends EntityRepository
{
    public function checkIfDocumentIsUsed(UserDocument $document): bool
    {
        $event = $this
            ->getEntityManager()->createQueryBuilder()
            ->from(Event::class, 'event')
            ->select('event.id')
            ->join('event.documents', 'documents')
            ->where('documents.id = :documentId')
            ->setParameter('documentId', $document->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        if ($event && $event[0] > 0) {
            return true;
        }

        $committeeFeed = $this
            ->getEntityManager()->createQueryBuilder()
            ->from(CommitteeFeedItem::class, 'committeeFeed')
            ->select('committeeFeed.id')
            ->join('committeeFeed.documents', 'documents')
            ->where('documents.id = :documentId')
            ->setParameter('documentId', $document->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return $committeeFeed && $committeeFeed[0] > 0;
    }
}

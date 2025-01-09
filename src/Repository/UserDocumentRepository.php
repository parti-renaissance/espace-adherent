<?php

namespace App\Repository;

use App\Entity\Event\CommitteeEvent;
use App\Entity\UserDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDocument::class);
    }

    public function checkIfDocumentIsUsed(UserDocument $document): bool
    {
        $event = $this
            ->getEntityManager()->createQueryBuilder()
            ->from(CommitteeEvent::class, 'event')
            ->select('event.id')
            ->join('event.documents', 'documents')
            ->where('documents.id = :documentId')
            ->setParameter('documentId', $document->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return $event && $event[0] > 0;
    }
}

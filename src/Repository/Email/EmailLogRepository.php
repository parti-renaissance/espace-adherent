<?php

namespace App\Repository\Email;

use App\Entity\Email\EmailLog;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class EmailLogRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailLog::class);
    }

    public function findOneByUuid(UuidInterface|string $uuid): ?EmailLog
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * @return EmailLog[]
     */
    public function findRecipientMessages(string $messageClass, string $recipient): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->andWhere('e.recipients LIKE :recipient')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', $this->getMessageType($messageClass))
            ->setParameter('recipient', '%'.$recipient.'%')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMostRecentMessage(?string $messageClass = null): ?EmailLog
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->setMaxResults(1)
            ->orderBy('e.createdAt', 'DESC')
        ;

        if ($messageClass) {
            $qb
                ->where('e.messageClass = :class')
                ->setParameter('class', $this->getMessageType($messageClass))
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return EmailLog[]
     */
    public function findMessages(string $messageClass): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', $this->getMessageType($messageClass))
            ->getQuery()
            ->getResult()
        ;
    }

    public function setDelivered(EmailLog $email, string $response): void
    {
        $email->delivered($response);

        $this->getEntityManager()->flush();
    }

    private function getMessageType(string $messageClass): string
    {
        $parts = explode('\\', $messageClass);

        return end($parts);
    }
}

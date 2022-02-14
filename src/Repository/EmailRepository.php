<?php

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmailRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }

    public function findOneByUuid(string $uuid): ?Email
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * @return Email[]
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

    public function findMostRecentMessage(string $messageClass = null): ?Email
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
     * @return Email[]
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

    public function setDelivered(Email $email, string $response): void
    {
        $email->delivered($response);

        $this->_em->flush();
    }

    private function getMessageType(string $messageClass): string
    {
        $parts = explode('\\', $messageClass);

        return end($parts);
    }
}

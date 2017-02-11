<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MailjetEmail;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Uuid;

class MailjetEmailRepository extends EntityRepository
{
    /**
     * Finds a MailjetEmail instance by its UUID.
     *
     * @param string $uuid
     *
     * @return MailjetEmail|null
     */
    public function findOneByUuid(string $uuid): ?MailjetEmail
    {
        return $this->findOneBy(['uuid' => Uuid::fromString($uuid)->toString()]);
    }

    /**
     * Finds a list of MailjetEmail instances having the same message batch UUID.
     *
     * @param string $uuid
     *
     * @return MailjetEmail[]
     */
    public function findByMessageBatchUuid(string $uuid): array
    {
        return $this->findBy(['messageBatchUuid' => Uuid::fromString($uuid)->toString()]);
    }

    public function findRecipientMessages(string $messageClass, string $recipient): array
    {
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->andWhere('e.recipient = :recipient')
            ->orderBy('e.sentAt', 'DESC')
            ->setParameter('class', $messageClass)
            ->setParameter('recipient', $recipient)
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function findMessages(string $messageClass, string $batch = null): array
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->orderBy('e.sentAt', 'DESC')
            ->setParameter('class', $messageClass);

        if ($batch) {
            $qb
                ->andWhere('e.messageBatchUuid = :batch')
                ->setParameter('batch', $batch);
        }

        return $qb->getQuery()->getResult();
    }
}

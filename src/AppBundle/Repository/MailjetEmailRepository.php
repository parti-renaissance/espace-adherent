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
    public function findByUuid(string $uuid)
    {
        $uuid = Uuid::fromString($uuid);

        return $this->findOneBy(['uuid' => $uuid->toString()]);
    }

    public function findRecipientMessages(string $messageClass, string $recipient)
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

    public function findMessages(string $messageClass, string $batch = null)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->orderBy('e.sentAt', 'DESC')
            ->setParameter('class', $messageClass);

        if ($batch) {
            $qb
                ->andWhere('e.batch = :batch')
                ->setParameter('batch', $batch);
        }

        return $qb->getQuery()->getResult();
    }
}

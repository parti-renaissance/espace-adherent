<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MailjetEmail;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Uuid;

class MailjetEmailRepository extends EntityRepository
{
    public function findOneByUuid(string $uuid): ?MailjetEmail
    {
        return $this->findOneBy(['uuid' => Uuid::fromString($uuid)->toString()]);
    }

    /**
     * @return MailjetEmail[]
     */
    public function findRecipientMessages(string $messageClass, string $recipient): array
    {
        $query = $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->andWhere('e.recipients LIKE :recipient')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', str_replace('AppBundle\\Mailjet\\Message\\', '', $messageClass))
            ->setParameter('recipient', '%'.$recipient.'%')
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * @return MailjetEmail[]
     */
    public function findMessages(string $messageClass, string $batch = null): array
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', str_replace('AppBundle\\Mailjet\\Message\\', '', $messageClass));

        if ($batch) {
            $qb
                ->andWhere('e.uuid = :batch')
                ->setParameter('batch', $batch);
        }

        return $qb->getQuery()->getResult();
    }
}

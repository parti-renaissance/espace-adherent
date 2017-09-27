<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MailjetEmail;
use Doctrine\ORM\EntityRepository;

class MailjetEmailRepository extends EntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?MailjetEmail
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * @param string $messageClass
     * @param string $recipient
     *
     * @return MailjetEmail[]
     */
    public function findRecipientMessages(string $messageClass, string $recipient): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->andWhere('e.recipients LIKE :recipient')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', str_replace('AppBundle\\Mailjet\\Message\\', '', $messageClass))
            ->setParameter('recipient', '%'.$recipient.'%')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMostRecentMessage(string $messageClass = null): ?MailjetEmail
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->setMaxResults(1)
            ->orderBy('e.createdAt', 'DESC')
        ;

        if ($messageClass) {
            $qb
                ->where('e.messageClass = :class')
                ->setParameter('class', str_replace('AppBundle\\Mailjet\\Message\\', '', $messageClass))
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $messageClass
     *
     * @return MailjetEmail[]
     */
    public function findMessages(string $messageClass): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', str_replace('AppBundle\\Mailjet\\Message\\', '', $messageClass))
            ->getQuery()
            ->getResult()
        ;
    }

    public function setDelivered(MailjetEmail $email, string $response): void
    {
        $email->delivered($response);

        $this->_em->flush();
    }
}

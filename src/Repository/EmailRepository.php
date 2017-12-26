<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Email;
use Doctrine\ORM\EntityRepository;

class EmailRepository extends EntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(string $uuid): ?Email
    {
        return $this->findOneByValidUuid($uuid);
    }

    /**
     * @param string $messageClass
     * @param string $recipient
     *
     * @return Email[]
     */
    public function findRecipientMessages(string $messageClass, string $recipient): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->andWhere('e.recipients LIKE :recipient')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', str_replace('AppBundle\\Mailer\\Message\\', '', $messageClass))
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
                ->setParameter('class', str_replace('AppBundle\\Mailer\\Message\\', '', $messageClass))
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $messageClass
     *
     * @return Email[]
     */
    public function findMessages(string $messageClass): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.messageClass = :class')
            ->orderBy('e.createdAt', 'DESC')
            ->setParameter('class', str_replace('AppBundle\\Mailer\\Message\\', '', $messageClass))
            ->getQuery()
            ->getResult()
            ;
    }

    public function setDelivered(Email $email, string $response): void
    {
        $email->delivered($response);

        $this->_em->flush();
    }
}

<?php

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EmailRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(RegistryInterface $registry)
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
            ->setParameter('class', str_replace('App\\Mailer\\Message\\', '', $messageClass))
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
                ->setParameter('class', str_replace('App\\Mailer\\Message\\', '', $messageClass))
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
            ->setParameter('class', str_replace('App\\Mailer\\Message\\', '', $messageClass))
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

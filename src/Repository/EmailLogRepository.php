<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EmailLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EmailLogRepository extends ServiceEntityRepository
{
    use ReferentTrait;
    use UuidEntityRepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EmailLog::class);
    }

    public function findSendedBy(Adherent $referent): array
    {
        return $this->createQueryBuilder('email')
            ->select('email')
            ->where('email.sender = :emailAddress')
            ->setParameter('emailAddress', $referent->getEmailAddress())
            ->orderBy('email.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}

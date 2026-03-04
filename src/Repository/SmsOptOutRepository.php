<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SmsOptOut;
use App\Mailchimp\Contact\SmsOptOutSourceEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SmsOptOutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmsOptOut::class);
    }

    public function isOptedOut(string $phone): bool
    {
        $lastOptOut = $this->createQueryBuilder('o')
            ->where('o.phone = :phone')
            ->setParameter('phone', $this->normalizePhone($phone))
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $lastOptOut && !$lastOptOut->isCancelled();
    }

    public function add(string $phone, SmsOptOutSourceEnum $source): void
    {
        $optOut = new SmsOptOut($this->normalizePhone($phone), $source);
        $em = $this->getEntityManager();
        $em->persist($optOut);
        $em->flush();
    }

    public function cancelLastActiveOptOut(string $phone): void
    {
        $lastActive = $this->createQueryBuilder('o')
            ->where('o.phone = :phone')
            ->andWhere('o.cancelledAt IS NULL')
            ->setParameter('phone', $this->normalizePhone($phone))
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($lastActive) {
            $lastActive->cancel();
            $this->getEntityManager()->flush();
        }
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\s+/', '', $phone);
    }
}

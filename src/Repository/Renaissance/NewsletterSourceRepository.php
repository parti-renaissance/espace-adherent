<?php

declare(strict_types=1);

namespace App\Repository\Renaissance;

use App\Entity\Renaissance\NewsletterSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsletterSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterSource::class);
    }

    public function findOneByCode(string $code): ?NewsletterSource
    {
        return $this->findOneBy(['code' => $code]);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Redirection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RedirectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Redirection::class);
    }

    public function findOneByOriginUri(string $url): ?Redirection
    {
        return $this->findOneBy(['from' => $url]);
    }

    /**
     * @return Redirection[]
     */
    public function findByTargetUri(string $url): array
    {
        return $this->findBy(['to' => $url]);
    }
}

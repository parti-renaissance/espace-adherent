<?php

namespace App\Repository;

use App\Entity\Redirection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RedirectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

<?php

declare(strict_types=1);

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Run;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class RunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Run::class);
    }

    public function findOneByUuid(UuidInterface|string $uuid): ?Run
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}

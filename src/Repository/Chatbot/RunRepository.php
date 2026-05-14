<?php

declare(strict_types=1);

namespace App\Repository\Chatbot;

use App\Entity\Chatbot\Run;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class RunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Run::class);
    }

    public function findOneByUuid(Uuid|string $uuid): ?Run
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Consultation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConsultationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consultation::class);
    }

    public function findOnePublished(string $uuid): ?Consultation
    {
        return $this->findOneBy(['uuid' => $uuid, 'published' => true]);
    }
}

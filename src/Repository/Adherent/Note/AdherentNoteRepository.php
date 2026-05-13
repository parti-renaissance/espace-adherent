<?php

declare(strict_types=1);

namespace App\Repository\Adherent\Note;

use App\Entity\Adherent\Note\AdherentNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentNote::class);
    }
}

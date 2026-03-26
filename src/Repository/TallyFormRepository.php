<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TallyForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TallyFormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TallyForm::class);
    }

    public function findOneBySlug(string $slug): ?TallyForm
    {
        return $this->findOneBy(['slug' => $slug, 'published' => true]);
    }
}

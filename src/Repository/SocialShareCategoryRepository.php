<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SocialShareCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\SocialShareCategory>
 */
class SocialShareCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialShareCategory::class);
    }

    /**
     * @return SocialShareCategory[]
     */
    public function findForWall(): array
    {
        return $this->findBy([], ['position' => 'ASC']);
    }
}

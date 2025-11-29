<?php

declare(strict_types=1);

namespace App\Repository\JeMengage;

use App\Entity\JeMengage\HeaderBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HeaderBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HeaderBlock::class);
    }
}

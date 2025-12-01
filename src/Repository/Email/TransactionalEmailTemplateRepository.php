<?php

declare(strict_types=1);

namespace App\Repository\Email;

use App\Entity\Email\TransactionalEmailTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Email\TransactionalEmailTemplate>
 */
class TransactionalEmailTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionalEmailTemplate::class);
    }
}

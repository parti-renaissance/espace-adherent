<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MailchimpSegment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MailchimpSegmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpSegment::class);
    }

    public function findOneForListByLabel(string $list, string $label): ?MailchimpSegment
    {
        return $this->findOneBy([
            'list' => $list,
            'label' => $label,
        ]);
    }

    public function findOneForElectedRepresentative(string $label): ?MailchimpSegment
    {
        return $this->findOneForListByLabel(MailchimpSegment::LIST_ELECTED_REPRESENTATIVE, $label);
    }
}

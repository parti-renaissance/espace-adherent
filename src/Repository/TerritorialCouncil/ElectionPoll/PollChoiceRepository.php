<?php

namespace App\Repository\TerritorialCouncil\ElectionPoll;

use App\Entity\TerritorialCouncil\ElectionPoll\PollChoice;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PollChoiceRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollChoice::class);
    }
}

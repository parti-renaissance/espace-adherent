<?php

namespace App\Repository\TerritorialCouncil\ElectionPoll;

use App\Entity\TerritorialCouncil\ElectionPoll\PollChoice;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PollChoiceRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PollChoice::class);
    }
}

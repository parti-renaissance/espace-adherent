<?php

namespace App\Repository\OAuth;

use App\AppCodeEnum;
use App\Entity\OAuth\Client;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function getCadreClient(): Client
    {
        return $this->findOneBy(['code' => AppCodeEnum::JEMENGAGE_WEB]);
    }

    public function getFormationClient(): Client
    {
        return $this->findOneBy(['code' => AppCodeEnum::FORMATION]);
    }

    public function getVoxClient(): Client
    {
        return $this->findOneBy(['code' => AppCodeEnum::BESOIN_D_EUROPE]);
    }
}

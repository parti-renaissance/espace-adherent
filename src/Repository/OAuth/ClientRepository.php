<?php

declare(strict_types=1);

namespace App\Repository\OAuth;

use App\AppCodeEnum;
use App\Entity\OAuth\Client;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\OAuth\Client>
 */
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

    public function getVoxClient(): Client
    {
        return $this->findOneBy(['code' => AppCodeEnum::BESOIN_D_EUROPE]);
    }
}

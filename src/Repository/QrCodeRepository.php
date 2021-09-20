<?php

namespace App\Repository;

use App\Entity\QrCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QrCodeRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QrCode::class);
    }

    public function findOneByUuid(string $uuid): ?QrCode
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function save(QrCode $qrCode): void
    {
        $this->_em->persist($qrCode);
        $this->_em->flush();
    }
}

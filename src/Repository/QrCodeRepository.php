<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QrCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\QrCode>
 */
class QrCodeRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QrCode::class);
    }

    public function findOneByUuid(UuidInterface|string $uuid): ?QrCode
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function save(QrCode $qrCode): void
    {
        $this->_em->persist($qrCode);
        $this->_em->flush();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Device;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Device>
 */
class DeviceRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    public function findOneByDeviceUuid(string $deviceUuid): ?Device
    {
        return $this->findOneBy(['deviceUuid' => $deviceUuid]);
    }

    public function save(Device $device): void
    {
        $this->_em->persist($device);
        $this->_em->flush();
    }
}

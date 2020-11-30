<?php

namespace App\Repository;

use App\Entity\Device;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DeviceRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Device::class);
    }

    public function findOneByDeviceUuid(string $deviceUuid): ?Device
    {
        self::validUuid($deviceUuid);

        return $this->findOneBy(['deviceUuid' => $deviceUuid]);
    }

    public function save(Device $device): void
    {
        $this->_em->persist($device);
        $this->_em->flush();
    }
}

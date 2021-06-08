<?php

namespace App\Device;

use App\Entity\Device;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeviceManager
{
    private $zoneRepository;
    private $entityManager;

    public function __construct(ZoneRepository $zoneRepository, EntityManagerInterface $entityManager)
    {
        $this->zoneRepository = $zoneRepository;
        $this->entityManager = $entityManager;
    }

    public function refreshZoneFromPostalCode(Device $device): void
    {
        $device->clearZones();

        if ($device->getPostalCode()) {
            $zone = $this->zoneRepository->findOneByPostalCode($device->getPostalCode());

            if ($zone) {
                $device->addZone($zone);
            }
        }

        $this->entityManager->flush();
    }
}

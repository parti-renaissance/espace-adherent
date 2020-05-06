<?php

namespace App\MunicipalManager\MunicipalManagerRole;

use App\Entity\Adherent;
use App\Entity\City;
use App\Entity\MunicipalManagerRoleAssociation;
use App\MunicipalManager\MunicipalManagerAssociationValueObject;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;

class MunicipalManagerAssociationManager
{
    private $adherentRepository;
    private $entityManager;

    public function __construct(AdherentRepository $adherentRepository, EntityManagerInterface $entityManager)
    {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param City[] $cities
     *
     * @return MunicipalManagerAssociationValueObject[]
     */
    public function getAssociationValueObjectsFromCities(array $cities): array
    {
        $municipalManagers = $this->adherentRepository->findMunicipalManagersForCities($cities);

        $data = [];

        foreach ($cities as $city) {
            $data[] = new MunicipalManagerAssociationValueObject($city, $municipalManagers[$city->getId()] ?? null);
        }

        return $data;
    }

    /**
     * @param MunicipalManagerAssociationValueObject[] $valueObjects
     */
    public function handleUpdate(array $valueObjects): void
    {
        $this->entityManager->beginTransaction();

        try {
            foreach ($valueObjects as $object) {
                $city = $object->getCity();
                $adherent = $object->getAdherent();
                /** @var Adherent|null $existingAdherent */
                $existingAdherent = current($this->adherentRepository->findMunicipalManagersForCities([$city]));

                if (
                    $existingAdherent
                    && (!$adherent || !$existingAdherent->equals($adherent))
                ) {
                    $this->removeMunicipalManagerCity($existingAdherent, $city);

                    $this->entityManager->flush();
                }

                if (
                    $adherent
                    && (!$existingAdherent || !$adherent->equals($existingAdherent))
                ) {
                    $this->addMunicipalManagerCity($adherent, $city);
                }
            }

            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        }
    }

    private function removeMunicipalManagerCity(Adherent $adherent, City $city): void
    {
        if (!$municipalManagerRole = $adherent->getMunicipalManagerRole()) {
            return;
        }

        $municipalManagerRole->removeCity($city);

        if ($municipalManagerRole->getCities()->isEmpty()) {
            $adherent->revokeMunicipalManager();
        }
    }

    private function addMunicipalManagerCity(Adherent $adherent, City $city): void
    {
        if ($adherent->isMunicipalManager()) {
            $adherent->getMunicipalManagerRole()->addCity($city);
        } else {
            $adherent->setMunicipalManagerRole(new MunicipalManagerRoleAssociation([$city]));
        }
    }
}

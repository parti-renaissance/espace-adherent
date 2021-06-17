<?php

namespace App\Instance\InstanceQualityUpdater;

use App\Entity\Adherent;
use App\Entity\Instance\AdherentInstanceQuality;
use App\Entity\Instance\InstanceQuality;
use App\Repository\Instance\InstanceQualityRepository;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractQualityUpdater implements QualityUpdaterInterface
{
    /** @var InstanceQuality[] */
    private static $instanceQualities = [];
    /** @var InstanceQualityRepository */
    private $instanceQualityRepository;
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @required */
    public function setInstanceQualityRepository(InstanceQualityRepository $instanceQualityRepository): void
    {
        $this->instanceQualityRepository = $instanceQualityRepository;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    final public function update(Adherent $adherent): void
    {
        $currentQualityCode = $this->getQuality();

        if ($this->isValid($adherent)) {
            $this->updateNewQuality($this->addQuality($adherent, $currentQualityCode));
        } else {
            $this->removeQuality($adherent, $currentQualityCode);
        }
    }

    abstract protected function isValid(Adherent $adherent): bool;

    abstract protected function getQuality(): string;

    protected function updateNewQuality(AdherentInstanceQuality $quality): void
    {
    }

    protected function removeQuality(Adherent $adherent, string $code): void
    {
        $adherent->removeInstanceQuality($this->findQualityByCode($code));
    }

    protected function addQuality(Adherent $adherent, string $code): AdherentInstanceQuality
    {
        return $adherent->addInstanceQuality($this->findQualityByCode($code));
    }

    private function findQualityByCode(string $code): ?InstanceQuality
    {
        if (!self::$instanceQualities) {
            foreach ($this->instanceQualityRepository->findAll() as $quality) {
                self::$instanceQualities[$quality->getCode()] = $quality;
            }
        }

        if (isset(self::$instanceQualities[$code])) {
            return $this->entityManager->getPartialReference(InstanceQuality::class, self::$instanceQualities[$code]->getId());
        }

        throw new \RuntimeException(sprintf('Instance quality with code "%s" not found', $code));
    }
}

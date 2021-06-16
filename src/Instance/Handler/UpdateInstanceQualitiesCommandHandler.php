<?php

namespace App\Instance\Handler;

use App\Instance\Command\UpdateInstanceQualitiesCommand;
use App\Instance\InstanceQualityUpdater\QualityUpdaterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateInstanceQualitiesCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    /** @var QualityUpdaterInterface[]|iterable */
    private $qualityUpdaters;

    public function __construct(EntityManagerInterface $entityManager, iterable $qualityUpdaters)
    {
        $this->entityManager = $entityManager;
        $this->qualityUpdaters = $qualityUpdaters;
    }

    public function __invoke(UpdateInstanceQualitiesCommand $command): void
    {
        $adherent = $command->getAdherent();

        foreach ($this->qualityUpdaters as $qualityUpdater) {
            $qualityUpdater->update($adherent);
        }

        $this->entityManager->flush();
    }
}

<?php

namespace App\ChezVous\Handler;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\ChezVous\Command\DeleteMeasureTypeOnAlgoliaCommand;
use App\ChezVous\Command\MeasureTypeCommandInterface;
use App\Repository\ChezVous\CityRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MeasureTypeCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly AlgoliaIndexedEntityManager $algoliaIndexedEntityManager
    ) {
    }

    public function __invoke(MeasureTypeCommandInterface $command): void
    {
        foreach (array_chunk($this->getCities($command), 1000) as $chunk) {
            $this->algoliaIndexedEntityManager->batch($chunk);
        }
    }

    private function getCities(MeasureTypeCommandInterface $command): array
    {
        if ($command instanceof DeleteMeasureTypeOnAlgoliaCommand) {
            return $this->cityRepository->findAll();
        }

        return $this->cityRepository->findAllByMeasureType($command->measureTypeId);
    }
}

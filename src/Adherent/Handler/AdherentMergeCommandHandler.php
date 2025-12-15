<?php

declare(strict_types=1);

namespace App\Adherent\Handler;

use App\Adherent\Command\AdherentMergeCommand;
use App\Adherent\Merge\AdherentMergeManager;
use App\Adherent\Merge\DynamicRelationMerger;
use App\Adherent\Merge\ProcessTracker;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AdherentMergeCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProcessTracker $tracker,
        private readonly AdherentRepository $repository,
        private readonly AdherentMergeManager $mergeManager,
        private readonly DynamicRelationMerger $relationMerger,
    ) {
    }

    public function __invoke(AdherentMergeCommand $command): void
    {
        if (!$source = $this->repository->find($command->adherentSourceId)) {
            return;
        }

        if (!$target = $this->repository->find($command->adherentTargetId)) {
            $this->mergeManager->clearCache($source);

            return;
        }

        $processId = (string) $source->getId();
        $this->tracker->log($processId, 'Démarrage...', 0);

        $this->relationMerger->migrateRelations($source, $target, function ($msg, $pourcent) use ($processId) {
            $this->tracker->log($processId, $msg, $pourcent);
        });

        $this->tracker->log($processId, 'Toutes les relations ont été déplacées.', 90);

        $target->merge($source);

        $this->em->remove($source);
        $this->em->flush();

        $this->tracker->log($processId, 'Fusion terminée.', 100);
    }
}

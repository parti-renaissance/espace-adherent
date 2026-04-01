<?php

declare(strict_types=1);

namespace App\ManagedUsers\Handler;

use App\ManagedUsers\Command\RefreshManagedUserProjectionCommand;
use App\ManagedUsers\ProjectionRefresher;
use App\Repository\AdherentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RefreshManagedUserProjectionCommandHandler
{
    public function __construct(
        private readonly ProjectionRefresher $projectionService,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(RefreshManagedUserProjectionCommand $command): void
    {
        if ($adherent = $this->adherentRepository->findOneByUuid($command->getUuid())) {
            $this->projectionService->refresh($adherent);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\BesoinDEurope\Inscription\Handler;

use App\BesoinDEurope\Inscription\Command\InscriptionConfirmationCommand;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InscriptionConfirmationCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly MembershipNotifier $membershipNotifier,
    ) {
    }

    public function __invoke(InscriptionConfirmationCommand $command): void
    {
        $adherent = $this->adherentRepository->findOneByUuid($command->getUuid());

        if (!$adherent) {
            return;
        }

        $this->membershipNotifier->sendConfirmationJoinMessage($adherent, false);
    }
}

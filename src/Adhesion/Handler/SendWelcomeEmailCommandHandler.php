<?php

namespace App\Adhesion\Handler;

use App\Adhesion\Command\SendWelcomeEmailCommand;
use App\Entity\Adherent;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendWelcomeEmailCommandHandler
{
    public function __construct(
        private readonly MembershipNotifier $membershipNotifier,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(SendWelcomeEmailCommand $command): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if ($adherent->isDisabled()) {
            return;
        }

        $this->membershipNotifier->sendConfirmationJoinMessage($adherent, $command->renew);
    }
}

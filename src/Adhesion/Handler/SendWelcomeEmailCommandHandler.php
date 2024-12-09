<?php

namespace App\Adhesion\Handler;

use App\Adherent\Tag\TagEnum;
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

        if (!$adherent->isEnabled()) {
            return;
        }

        if (!$adherent->hasTag(TagEnum::getAdherentYearTag(tag: TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN))) {
            return;
        }

        $this->membershipNotifier->sendConfirmationJoinMessage($adherent);
    }
}

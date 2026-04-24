<?php

declare(strict_types=1);

namespace App\Event\Handler;

use App\Adherent\Tag\TagEnum;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Entity\Event\RegistrationStatusEnum;
use App\Event\Command\InviteAdherentForAllFutureInvitationEventsCommand;
use App\Event\EventInvitationNotifier;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Repository\AdherentRepository;
use App\Repository\AgoraRepository;
use App\Repository\CommitteeRepository;
use App\Repository\Event\EventRepository;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InviteAdherentForAllFutureInvitationEventsCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly AgoraRepository $agoraRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly EventRepository $eventRepository,
        private readonly EventRegistrationCommandHandler $registrationHandler,
        private readonly EventInvitationNotifier $notifier,
    ) {
    }

    public function __invoke(InviteAdherentForAllFutureInvitationEventsCommand $command): void
    {
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if (!$adherent->isEnabled() || !$adherent->hasTag(TagEnum::ADHERENT) || !$adherent->hasSubscriptionType(SubscriptionTypeEnum::EVENT_EMAIL)) {
            return;
        }

        $container = $this->resolveContainer($command);

        if (null === $container || !$this->isContainerEligible($container)) {
            return;
        }

        $events = $this->eventRepository->findAllFutureInvitationEventsWithoutAdherent($container, $adherent, new \DateTime());

        foreach ($events as $event) {
            if ($this->registrationHandler->handle(new EventRegistrationCommand($event, $adherent, RegistrationStatusEnum::INVITED), false)) {
                $this->notifier->sendEventInvitation($event, [$adherent]);
            }
        }
    }

    private function resolveContainer(InviteAdherentForAllFutureInvitationEventsCommand $command): Agora|Committee|null
    {
        if (null !== $command->agoraId) {
            return $this->agoraRepository->find($command->agoraId);
        }

        if (null !== $command->committeeId) {
            return $this->committeeRepository->find($command->committeeId);
        }

        return null;
    }

    private function isContainerEligible(Agora|Committee $container): bool
    {
        return $container instanceof Agora ? $container->published : $container->isApproved();
    }
}

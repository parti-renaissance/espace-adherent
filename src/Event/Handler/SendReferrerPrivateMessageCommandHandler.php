<?php

namespace App\Event\Handler;

use App\Entity\TimelineItemPrivateMessage;
use App\Event\Command\SendReferrerPrivateMessageCommand;
use App\JeMengage\Push\Command\PrivateMessageNotificationCommand;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SendReferrerPrivateMessageCommandHandler
{
    public function __construct(
        private readonly EventRegistrationRepository $registrationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(SendReferrerPrivateMessageCommand $command): void
    {
        if (!$eventRegistration = $this->registrationRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        if (!$eventRegistration->referrer) {
            return;
        }

        $privateMessage = new TimelineItemPrivateMessage([$eventRegistration->referrer]);

        $privateMessage->title = $privateMessage->notificationTitle = \sprintf(
            '%s %s s\'est inscrit à %s depuis le lien que vous avez partagé.',
            $eventRegistration->getFirstName(),
            $eventRegistration->getLastName(),
            $eventRegistration->getEvent()->getName()
        );
        $privateMessage->description = $privateMessage->notificationDescription = 'En partageant des liens d\'événements, vous participez activement au dynamisme de notre parti, merci !';

        $privateMessage->ctaLabel = 'Voir l\'événement';
        $privateMessage->ctaUrl = \sprintf('/evenements/%s', $eventRegistration->getEvent()->getSlug());

        $privateMessage->source = \sprintf('inscription événement [%d] - parrainage', $eventRegistration->getId());

        $this->entityManager->persist($privateMessage);
        $this->entityManager->flush();

        $this->bus->dispatch(new PrivateMessageNotificationCommand($privateMessage->getUuid()));
    }
}

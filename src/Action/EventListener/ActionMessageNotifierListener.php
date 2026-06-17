<?php

declare(strict_types=1);

namespace App\Action\EventListener;

use App\Action\ActionEvent;
use App\Action\Command\SendActionCreationNotificationCommand;
use App\Entity\Action\Action;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\ActionCancellationMessage;
use App\Mailer\Message\Renaissance\ActionUpdateMessage;
use App\Repository\Action\ActionParticipantRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActionMessageNotifierListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ActionParticipantRepository $participantRepository,
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ACTION_CREATED => 'onActionCreated',
            Events::ACTION_UPDATED => 'onActionUpdated',
            Events::ACTION_CANCELLED => 'onActionCancelled',
        ];
    }

    public function onActionCreated(ActionEvent $event): void
    {
        $this->bus->dispatch(new SendActionCreationNotificationCommand($event->getAction()->getUuid()));
    }

    public function onActionUpdated(ActionEvent $event): void
    {
        $this->notifyParticipants($event->getAction(), ActionUpdateMessage::class);
    }

    public function onActionCancelled(ActionEvent $event): void
    {
        $this->notifyParticipants($event->getAction(), ActionCancellationMessage::class);
    }

    /**
     * @param class-string<ActionUpdateMessage|ActionCancellationMessage> $messageClass
     */
    private function notifyParticipants(Action $action, string $messageClass): void
    {
        if (!$recipients = $this->participantRepository->findParticipantAdherents($action)) {
            return;
        }

        $actionUrl = $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).'actions/'.$action->getUuid()->toRfc4122();

        foreach (array_chunk($recipients, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage($messageClass::create($chunk, $action, $actionUrl));
        }
    }
}

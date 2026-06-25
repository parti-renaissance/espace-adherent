<?php

declare(strict_types=1);

namespace App\Action\Handler;

use App\Action\Command\SendActionParticipantsNotificationCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\ActionCancellationMessage;
use App\Mailer\Message\Renaissance\ActionUpdateMessage;
use App\Repository\Action\ActionParticipantRepository;
use App\Repository\Action\ActionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendActionParticipantsNotificationCommandHandler
{
    public function __construct(
        private readonly ActionRepository $actionRepository,
        private readonly ActionParticipantRepository $participantRepository,
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(SendActionParticipantsNotificationCommand $command): void
    {
        if (!$action = $this->actionRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if (!$command->isCancelled() && $action->isCancelled()) {
            return;
        }

        if (!$recipients = $this->participantRepository->findParticipantAdherents($action)) {
            return;
        }

        $messageClass = $command->isCancelled() ? ActionCancellationMessage::class : ActionUpdateMessage::class;
        $actionUrl = $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).'actions/'.$action->getUuid()->toRfc4122();

        foreach (array_chunk($recipients, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage($messageClass::create($chunk, $action, $actionUrl));
        }
    }
}

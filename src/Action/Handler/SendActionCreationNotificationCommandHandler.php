<?php

declare(strict_types=1);

namespace App\Action\Handler;

use App\Action\Command\SendActionCreationNotificationCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\ActionNotificationMessage;
use App\Repository\Action\ActionRepository;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendActionCreationNotificationCommandHandler
{
    public function __construct(
        private readonly ActionRepository $actionRepository,
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(SendActionCreationNotificationCommand $command): void
    {
        if (!$action = $this->actionRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if ($action->isCancelled()) {
            return;
        }

        if (!$author = $action->getAuthor()) {
            return;
        }

        if (!$communeZones = $action->getCityOrBoroughZones()) {
            return;
        }

        $recipients = $this->adherentRepository->findMembersAndAdherentsInZones($communeZones, SubscriptionTypeEnum::EVENT_EMAIL);

        if (!$recipients) {
            return;
        }

        $actionUrl = $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).'actions/'.$action->getUuid()->toRfc4122();

        foreach (array_chunk($recipients, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage(ActionNotificationMessage::create(
                $chunk,
                $author,
                $action,
                $actionUrl,
            ));
        }
    }
}

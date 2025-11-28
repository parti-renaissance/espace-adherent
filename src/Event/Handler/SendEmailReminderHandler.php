<?php

declare(strict_types=1);

namespace App\Event\Handler;

use App\Entity\Event\EventRegistration;
use App\Event\Command\SendEmailReminderCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\EventReminderMessage;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendEmailReminderHandler
{
    public function __construct(
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailerService $transactionalMailer,
    ) {
    }

    public function __invoke(SendEmailReminderCommand $command): void
    {
        /** @var EventRegistration $eventRegistration */
        if (!$eventRegistration = $this->eventRegistrationRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        $this->entityManager->refresh($eventRegistration);

        $this->transactionalMailer->sendMessage(
            EventReminderMessage::create(
                $eventRegistration,
                $this->urlGenerator->generate('vox_app', [], UrlGeneratorInterface::ABSOLUTE_URL).'evenements/'.$eventRegistration->getEvent()->getSlug(),
            ),
            false
        );
    }
}

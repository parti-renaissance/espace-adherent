<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Handler;

use App\Repository\AdherentRepository;
use App\Ses\Webhook\Command\ProcessSesNotificationCommand;
use App\Ses\Webhook\SesFeedbackType;
use App\Ses\Webhook\SesNotificationParser;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessSesNotificationCommandHandler
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly SesNotificationParser $parser,
        private readonly AdherentRepository $adherentRepository,
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly EntityManagerInterface $entityManager,
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    public function __invoke(ProcessSesNotificationCommand $command): void
    {
        $payload = $command->payload;
        $event = $this->parser->parse($payload);

        if (null === $event) {
            if ($this->parser->describesFeedback($payload)) {
                $this->logger->error('[SES][Webhook] Actionable feedback could not be parsed', [
                    'message_id' => $payload['MessageId'] ?? null,
                ]);
            }

            return;
        }

        $changed = false;
        foreach ($event->recipients as $email) {
            $adherent = $this->adherentRepository->findOneByEmail($email);
            if (null === $adherent) {
                continue;
            }

            if (SesFeedbackType::HARD_BOUNCE === $event->type) {
                $adherent->markAsEmailHardBounced();
            } else {
                // A complaint is recorded as such (distinct from a voluntary unsubscribe) and also
                // withdraws consent (markAsUnsubscribe => UNSUBSCRIBED), keeping the invariant
                // complained => unsubscribed.
                $adherent->markAsEmailComplained();
                $adherent->markAsUnsubscribe();
                $this->subscriptionHandler->handleUpdateSubscription($adherent, []);
            }

            $changed = true;
        }

        if ($changed) {
            $this->entityManager->flush();
        }
    }
}

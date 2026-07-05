<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Repository\AdherentRepository;
use App\Ses\Webhook\SesEventType;
use App\Ses\Webhook\SesFeedbackType;
use App\Ses\Webhook\SesNotificationParser;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ses_event_processor')]
class SuppressionProcessor implements SesEventProcessorInterface
{
    public function __construct(
        private readonly SesNotificationParser $parser,
        private readonly AdherentRepository $adherentRepository,
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function supports(SesEventType $type): bool
    {
        return SesEventType::Bounce === $type || SesEventType::Complaint === $type;
    }

    public function supportsDirectNotification(): bool
    {
        return true;
    }

    public function process(array $payload): void
    {
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

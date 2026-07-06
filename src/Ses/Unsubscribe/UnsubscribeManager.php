<?php

declare(strict_types=1);

namespace App\Ses\Unsubscribe;

use App\Entity\Adherent;
use App\History\UserActionHistoryHandler;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionHandler;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class UnsubscribeManager
{
    public function __construct(
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly AdherentRepository $adherentRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly string $secret,
    ) {
    }

    public function resolve(string $token): ?UnsubscribeContext
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Throwable) {
            // SignatureInvalidException, UnexpectedValueException, ExpiredException, etc.
            return null;
        }

        $uuid = $payload->uuid ?? null;
        if (!\is_string($uuid) || !Uuid::isValid($uuid)) {
            return null;
        }

        $adherent = $this->adherentRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);
        if (null === $adherent) {
            return null;
        }

        return new UnsubscribeContext($adherent, $this->readMemberId($payload), $this->readMessageUuid($payload));
    }

    public function unsubscribe(Adherent $adherent, ?int $memberId, ?string $messageUuid): void
    {
        // Idempotence: do not rewrite the audit timestamps on a redelivery or a scanner POST.
        if (ContactStatusEnum::UNSUBSCRIBED === $adherent->getMailchimpStatus()) {
            return;
        }

        // Keep the SMS consent (obtained separately, distinct GDPR proof); cut all emails only.
        $keptCodes = array_values(array_intersect(
            $adherent->getSubscriptionTypeCodes(),
            SubscriptionTypeEnum::DEFAULT_MOBILE_TYPES
        ));

        $adherent->markAsUnsubscribe();

        $this->subscriptionHandler->handleUpdateSubscription($adherent, $keptCodes);

        // Explicit flush is required: handleUpdateSubscription only flushes when the type list differs,
        // so it would not persist the markAsUnsubscribe() changes when no email type remained.
        $this->em->flush();

        $this->attributeToSend($memberId);
        $this->recordDurableAudit($adherent, $messageUuid);
    }

    private function attributeToSend(?int $memberId): void
    {
        if (null === $memberId) {
            return;
        }

        try {
            $this->memberRepository->markUnsubscribedById($memberId, new \DateTimeImmutable());
        } catch (\Throwable $exception) {
            $this->logger->error('[SES][Unsubscribe] Failed to attribute unsubscribe to the sent row', [
                'member_id' => $memberId,
                'exception' => $exception,
            ]);
        }
    }

    private function recordDurableAudit(Adherent $adherent, ?string $messageUuid): void
    {
        if (null === $messageUuid) {
            return;
        }

        try {
            $message = $this->adherentMessageRepository->findOneByUuid($messageUuid);
            if (null === $message) {
                // Message deleted since the send: nothing left to snapshot for the audit.
                return;
            }

            $this->userActionHistoryHandler->createEmailUnsubscribe($adherent, $message);
        } catch (\Throwable $exception) {
            $this->logger->error('[SES][Unsubscribe] Failed to record the durable unsubscribe audit', [
                'message_uuid' => $messageUuid,
                'exception' => $exception,
            ]);
        }
    }

    private function readMemberId(object $payload): ?int
    {
        $memberId = $payload->member_id ?? null;

        return \is_int($memberId) ? $memberId : null;
    }

    private function readMessageUuid(object $payload): ?string
    {
        $messageUuid = $payload->message_uuid ?? null;

        return \is_string($messageUuid) && Uuid::isValid($messageUuid) ? $messageUuid : null;
    }
}

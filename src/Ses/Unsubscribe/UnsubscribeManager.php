<?php

declare(strict_types=1);

namespace App\Ses\Unsubscribe;

use App\Entity\Adherent;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionHandler;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Uid\Uuid;

class UnsubscribeManager
{
    public function __construct(
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $em,
        private readonly string $secret,
    ) {
    }

    public function resolveAdherent(string $token): ?Adherent
    {
        try {
            $uuid = JWT::decode($token, new Key($this->secret, 'HS256'))->uuid ?? null;
        } catch (\Throwable) {
            // SignatureInvalidException, UnexpectedValueException, ExpiredException, etc.
            return null;
        }

        if (!\is_string($uuid) || !Uuid::isValid($uuid)) {
            return null;
        }

        return $this->adherentRepository->findOneBy(['uuid' => Uuid::fromString($uuid)]);
    }

    public function unsubscribe(Adherent $adherent): void
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
    }
}

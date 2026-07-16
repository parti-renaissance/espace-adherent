<?php

declare(strict_types=1);

namespace App\NationalEvent\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use Symfony\Component\Uid\Uuid;

class PaymentStatusUpdateCommand implements AsynchronousMessageInterface, LockableMessageInterface
{
    private const UNKNOWN_REFERENCE = 'unknown';

    /**
     * @param array $payload a Worldline payment payload, as returned by the API or carried by a webhook event
     */
    public function __construct(public readonly array $payload)
    {
    }

    /**
     * Our own payment uuid, sent to Worldline as the order merchant reference and echoed back in every payload.
     */
    public function getMerchantReference(): ?string
    {
        $reference = $this->payload['paymentOutput']['references']['merchantReference'] ?? null;

        return \is_string($reference) && Uuid::isValid($reference) ? $reference : null;
    }

    /**
     * The browser return and the webhook race to apply a status to the same payment, each on its own worker: without
     * this lock both would append a status and dispatch a success event.
     */
    public function getLockKey(): string
    {
        return 'national_event_payment_status_'.($this->getMerchantReference() ?? self::UNKNOWN_REFERENCE);
    }

    public function getLockTtl(): int
    {
        return 60;
    }

    public function isLockBlocking(): bool
    {
        return true;
    }
}

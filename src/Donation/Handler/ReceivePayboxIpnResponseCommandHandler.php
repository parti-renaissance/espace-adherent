<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Donation\Command\ReceivePayboxIpnResponseCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ReceivePayboxIpnResponseCommandHandler implements PaymentIpnHandlerInterface
{
    private const HIGH_VALUE_THRESHOLD = 1000_00; // 1000€ en centimes

    public function __construct(
        private readonly PaymentTransactionService $transactionService,
        private readonly DonationNotificationService $notificationService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ReceivePayboxIpnResponseCommand $command): void
    {
        $payload = $command->payload;

        if (!$donation = $this->transactionService->findDonationFromPayload($payload)) {
            $this->logger->error('[IPN] Donation not found', ['payload' => $payload]);

            return;
        }

        if ($this->transactionService->transactionExists($payload['transaction'])) {
            $this->logger->error('[IPN] Transaction already exists', ['payload' => $payload]);

            return;
        }

        $transaction = $this->transactionService->processPayment($donation, $payload);

        // Notification admin pour les gros montants
        if ($transaction->isSuccessful() && ($payload['amount'] ?? 0) >= self::HIGH_VALUE_THRESHOLD) {
            $this->notificationService->notifyAdminForHighValueDonation($donation);
        }
    }

    public function handle(ReceivePayboxIpnResponseCommand $command): void
    {
        $this->__invoke($command);
    }
}

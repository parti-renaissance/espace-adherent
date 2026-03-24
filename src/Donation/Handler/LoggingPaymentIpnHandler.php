<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Donation\Command\ReceivePayboxIpnResponseCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * Decorator qui ajoute du logging et de la supervision au handler IPN.
 */
#[AsDecorator(decorates: ReceivePayboxIpnResponseCommandHandler::class)]
class LoggingPaymentIpnHandler extends ReceivePayboxIpnResponseCommandHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SlackNotifier $slackNotifier,
    ) {
    }

    public function __invoke(ReceivePayboxIpnResponseCommand $command): void
    {
        $this->logger->info('[IPN] Processing payment notification', [
            'payload' => $command->payload,
        ]);

        $startTime = microtime(true);

        try {
            parent::__invoke($command);

            $duration = microtime(true) - $startTime;
            $this->logger->info('[IPN] Payment processed successfully', [
                'duration_ms' => round($duration * 1000, 2),
                'transaction_id' => $command->payload['transaction'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;

            $this->logger->error('[IPN] Payment processing failed', [
                'error' => $e->getMessage(),
                'duration_ms' => round($duration * 1000, 2),
                'payload' => $command->payload,
            ]);

            // Alerter l'équipe en cas d'erreur
            $this->slackNotifier->notifyError('IPN Handler Exception', [
                'exception' => $e->getMessage(),
                'payload' => $command->payload,
            ]);

            throw $e;
        }
    }

    public function handle(ReceivePayboxIpnResponseCommand $command): void
    {
        $this->__invoke($command);
    }
}

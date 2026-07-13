<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Handler;

use App\Ses\Campaign\Message\ReconcileSendErroredRowMessage;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class ReconcileSendErroredRowHandler
{
    /** Second and last look, long enough to cover a mail SES kept retrying (DeliveryDelay) before reporting. */
    private const int SECOND_PASS_DELAY_MS = 6 * 3_600 * 1_000;

    private LoggerInterface $logger;

    public function __construct(
        private readonly SendErroredRowReconciler $reconciler,
        private readonly MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(ReconcileSendErroredRowMessage $message): void
    {
        if ($this->reconciler->reconcile($message->rowId)) {
            return;
        }

        if (0 === $message->attempt) {
            $this->bus->dispatch(
                new ReconcileSendErroredRowMessage($message->rowId, 1),
                [new DelayStamp(self::SECOND_PASS_DELAY_MS)],
            );

            return;
        }

        $this->logger->error('[SES][Campaign] Quarantined row still unconfirmed — no SES event after the second pass, the mail may never have been sent', [
            'row' => $message->rowId,
        ]);
    }
}

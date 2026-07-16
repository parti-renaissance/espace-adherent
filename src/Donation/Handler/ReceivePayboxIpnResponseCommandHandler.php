<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Adhesion\Events\NewCotisationEvent;
use App\Donation\Command\ReceivePayboxIpnResponseCommand;
use App\Entity\Donation;
use App\Entity\NationalEvent\Payment;
use App\Mailer\MailerService;
use App\Mailer\Message\DonationThanksMessage;
use App\NationalEvent\Payment\PaymentConfirmationApplier;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\DonationRepository;
use App\Repository\NationalEvent\PaymentRepository as NationalEventPaymentRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class ReceivePayboxIpnResponseCommandHandler
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly EntityManagerInterface $manager,
        private readonly TransactionRepository $transactionRepository,
        private readonly DonationRepository $donationRepository,
        private readonly NationalEventPaymentRepository $nationalEventPaymentRepository,
        private readonly PaymentConfirmationApplier $confirmationApplier,
        private readonly MessageBusInterface $bus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ReceivePayboxIpnResponseCommand $command): void
    {
        $payload = $command->payload;

        if (!$donation = $this->getDonation($payload)) {
            $this->logger->error('[IPN] Donation not found', ['payload' => $payload]);

            return;
        }

        if ($this->transactionRepository->findByPayboxTransactionId($payload['transaction'])) {
            $this->logger->error('[IPN] Transaction already exists', ['payload' => $payload]);

            return;
        }

        $connection = $this->manager->getConnection();
        $connection->beginTransaction();

        try {
            $adherent = $donation->getDonator()?->getAdherent();

            $transaction = $donation->processPayload($payload);

            // A national event inscription is not a donation: it must never become the donator's last successful one.
            if ($transaction->isSuccessful() && !$donation->isForNationalEvent()) {
                $donation->markAsLastSuccessfulDonation();
            }

            $this->manager->persist($transaction);
            $this->manager->flush();

            if ($transaction->isSuccessful()) {
                if ($donation->isForNationalEvent()) {
                    // No adherent tag refresh and no donation email here: an inscription payment changes nothing
                    // about the donator, and its own confirmation email is sent by the inscription success event.
                    $this->confirmInscriptionPayment($donation, $payload);
                } elseif ($donation->isMembership()) {
                    if (!$adherent) {
                        $this->logger->error('Adhesion RE: adherent introuvable pour une cotisation réussie, donation id '.$donation->getId());

                        return;
                    }

                    $this->bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));

                    $this->eventDispatcher->dispatch(new NewCotisationEvent($adherent, $donation));
                } else {
                    if ($adherent) {
                        $this->bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));
                    }

                    if (
                        !$donation->hasSubscription()
                        || $donation->isFirstSuccessfulTransaction($transaction)
                    ) {
                        $this->transactionalMailer->sendMessage(DonationThanksMessage::createFromTransaction($transaction));
                    }
                }
            }

            $connection->commit();
        } catch (\Throwable $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Confirms the national event inscription this donation stands for. Mirrors the Worldline webhook: the applier
     * dispatches SuccessPaymentEvent, which is what actually sends the inscription confirmation email.
     */
    private function confirmInscriptionPayment(Donation $donation, array $payload): void
    {
        $payment = $this->nationalEventPaymentRepository->findOneBy(['donation' => $donation]);

        if (!$payment instanceof Payment) {
            $this->logger->error('[IPN] National event payment not found for donation {id}', ['id' => $donation->getId()]);

            return;
        }

        // Defence in depth only, never blocking: the signed form fixes PBX_TOTAL and Paybox echoes it back on a
        // signed IPN. The unit of the returned amount is unproven (no existing code reads it), so a mismatch is
        // reported rather than acted upon — rejecting on a wrong assumption would decline every real payment.
        if (isset($payload['amount']) && (int) $payload['amount'] !== $payment->amount) {
            $this->logger->error('[IPN] Paybox amount mismatch for payment {uuid}: got {got}, expected {expected}.', [
                'uuid' => $payment->getUuid()->toRfc4122(),
                'got' => $payload['amount'],
                'expected' => $payment->amount,
            ]);
        }

        // Always CONFIRMED: we only get here for a successful transaction. A failed payment leaves the payment
        // PENDING on purpose — the payer retries through a new one, and the failure is traced on the transaction.
        // Replayed IPNs never reach this point: findByPayboxTransactionId() already returned early above.
        $this->confirmationApplier->apply($payment, PaymentStatusEnum::CONFIRMED, $payload, null, $payload['result'] ?? null);
    }

    private function getDonation(array $payload): ?Donation
    {
        $donationUuid = isset($payload['id']) ? explode('_', $payload['id'], 2)[0] : null;

        if (!$donationUuid || !Uuid::isValid($donationUuid)) {
            return null;
        }

        return $this->donationRepository->findOneByUuid($donationUuid);
    }
}

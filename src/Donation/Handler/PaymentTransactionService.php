<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Entity\Donation;
use App\Entity\Transaction;
use App\Repository\DonationRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Service responsable de la gestion des transactions de paiement.
 */
class PaymentTransactionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionRepository $transactionRepository,
        private readonly DonationRepository $donationRepository,
        private readonly DonationNotificationService $notificationService,
    ) {
    }

    public function findDonationFromPayload(array $payload): ?Donation
    {
        $donationUuid = isset($payload['id']) ? explode('_', $payload['id'], 2)[0] : null;

        if (!$donationUuid || !Uuid::isValid($donationUuid)) {
            return null;
        }

        return $this->donationRepository->findOneByUuid($donationUuid);
    }

    public function transactionExists(string $transactionId): bool
    {
        return null !== $this->transactionRepository->findByPayboxTransactionId($transactionId);
    }

    public function processPayment(Donation $donation, array $payload): Transaction
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            $transaction = $donation->processPayload($payload);

            if ($transaction->isSuccessful()) {
                $donation->markAsLastSuccessfulDonation();
            }

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            // Envoyer les notifications si succès
            if ($transaction->isSuccessful()) {
                $this->notificationService->handleSuccessfulTransaction($donation, $transaction);
            }

            $this->entityManager->getConnection()->commit();

            return $transaction;
        } catch (\Throwable $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->getConnection()->rollBack();
            }

            throw $e;
        }
    }
}

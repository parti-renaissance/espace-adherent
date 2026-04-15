<?php

declare(strict_types=1);

namespace App\Donation;

use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Transaction;
use App\Repository\DonationRepository;
use App\Repository\TransactionRepository;

class DonationManager
{
    public function __construct(
        private readonly DonationRepository $donationRepository,
        private readonly TransactionRepository $transactionRepository,
    ) {
    }

    /**
     * @return DonationHistoryItem[]
     */
    public function getHistory(Adherent $adherent, bool $onlySuccess = true): array
    {
        $history = [];

        $transactions = $this->transactionRepository->findAllTransactionByAdherent($adherent, $onlySuccess);
        foreach ($transactions as $transaction) {
            $donation = $transaction->getDonation();
            $status = Transaction::PAYBOX_SUCCESS === $transaction->getPayboxResultCode()
                ? Donation::STATUS_FINISHED
                : $donation->getStatus();

            $history[] = $this->createDonationHistoryItem(
                $transaction->getPayboxDateTime() ?? $transaction->getCreatedAt(),
                $donation,
                $status
            );
        }

        $otherDonations = $this->donationRepository->findOfflineDonationsByAdherent($adherent);
        foreach ($otherDonations as $donation) {
            $history[] = $this->createDonationHistoryItem(
                $donation->getDonatedAt() ?? $donation->getCreatedAt(),
                $donation,
                $donation->getStatus()
            );
        }

        usort($history, function (DonationHistoryItem $donation1, DonationHistoryItem $donation2) {
            return $donation2->getDate() <=> $donation1->getDate();
        });

        return $history;
    }

    private function createDonationHistoryItem(
        \DateTimeInterface $date,
        Donation $donation,
        string $status,
    ): DonationHistoryItem {
        $donator = $donation->getDonator();

        return new DonationHistoryItem(
            $date,
            $donation->getAmount() ?? 0,
            $donation->getType() ?? '',
            DonationSemanticType::fromDonation($donation),
            DonationGlobalStatus::fromDonationStatus($status),
            $donation->getUuid(),
            $donator?->getId(),
            $donator?->getFullName(),
            $donator?->getIdentifier(),
        );
    }
}

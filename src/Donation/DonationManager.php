<?php

namespace App\Donation;

use App\Entity\Adherent;
use App\Repository\DonationRepository;
use App\Repository\TransactionRepository;

class DonationManager
{
    public function __construct(
        private readonly DonationRepository $donationRepository,
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function getHistory(Adherent $adherent): array
    {
        $history = [];

        $transactions = $this->transactionRepository->findAllSuccessfulTransactionByAdherentIdOrEmail($adherent);
        foreach ($transactions as $transaction) {
            $donation = $transaction->getDonation();

            $history[] = $this->createDonationValueObject(
                $transaction->getPayboxDateTime(),
                $donation->getAmount(),
                $donation->getType(),
                $donation->isSubscription(),
                $donation->isMembership()
            );
        }

        $otherDonations = $this->donationRepository->findOfflineDonationsByAdherent($adherent);
        foreach ($otherDonations as $donation) {
            $history[] = $this->createDonationValueObject(
                $donation->getDonatedAt(),
                $donation->getAmount(),
                $donation->getType(),
                $donation->isSubscription(),
                $donation->isMembership()
            );
        }

        usort($history, function (DonationValueObject $donation1, DonationValueObject $donation2) {
            return $donation2->getDate() <=> $donation1->getDate();
        });

        return $history;
    }

    private function createDonationValueObject(
        \DateTimeInterface $date,
        int $amount,
        string $type,
        bool $isSubscription,
        bool $isMembership
    ): DonationValueObject {
        return new DonationValueObject($date, $amount, $type, $isSubscription, $isMembership);
    }
}

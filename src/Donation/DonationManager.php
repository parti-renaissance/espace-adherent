<?php

namespace App\Donation;

use App\Entity\Adherent;
use App\Repository\DonationRepository;
use App\Repository\TransactionRepository;

class DonationManager
{
    private $donationRepository;
    private $transactionRepository;

    public function __construct(DonationRepository $donationRepository, TransactionRepository $transactionRepository)
    {
        $this->donationRepository = $donationRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getHistory(Adherent $adherent): array
    {
        $email = $adherent->getEmailAddress();

        $history = [];

        $transactions = $this->transactionRepository->findAllSuccessfulTransactionByEmail($email);
        foreach ($transactions as $transaction) {
            $donation = $transaction->getDonation();

            $history[] = $this->createDonationValueObject(
                $transaction->getPayboxDateTime(),
                $donation->getAmount(),
                $donation->getType(),
                $donation->isSubscription()
            );
        }

        $otherDonations = $this->donationRepository->findOfflineDonationsByEmail($email);
        foreach ($otherDonations as $donation) {
            $history[] = $this->createDonationValueObject(
                $donation->getDonatedAt(),
                $donation->getAmount(),
                $donation->getType(),
                $donation->isSubscription()
            );
        }

        usort($history, function (DonationValueObject $donation1, DonationValueObject $donation2) {
            return $donation1->getDate() > $donation2->getDate() ? -1 : 1;
        });

        return $history;
    }

    private function createDonationValueObject(
        \DateTimeInterface $date,
        int $amount,
        string $type,
        bool $isSubscription
    ): DonationValueObject {
        return new DonationValueObject($date, $amount, $type, $isSubscription);
    }
}

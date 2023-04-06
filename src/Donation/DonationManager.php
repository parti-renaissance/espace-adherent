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
                $donation->isSubscription(),
                $donation->isMembership()
            );
        }

        $otherDonations = $this->donationRepository->findOfflineDonationsByEmail($email);
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

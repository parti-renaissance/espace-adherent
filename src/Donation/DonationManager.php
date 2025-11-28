<?php

declare(strict_types=1);

namespace App\Donation;

use App\Entity\Adherent;
use App\Entity\Donator;
use App\Repository\DonationRepository;
use App\Repository\TransactionRepository;
use Ramsey\Uuid\UuidInterface;

class DonationManager
{
    public function __construct(
        private readonly DonationRepository $donationRepository,
        private readonly TransactionRepository $transactionRepository,
    ) {
    }

    public function getHistory(Adherent $adherent, bool $onlySuccess = true): array
    {
        $history = [];

        $transactions = $this->transactionRepository->findAllTransactionByAdherentIdOrEmail($adherent, $onlySuccess);
        foreach ($transactions as $transaction) {
            $donation = $transaction->getDonation();

            $history[] = $this->createDonationValueObject(
                $transaction->getPayboxDateTime() ?? $transaction->getCreatedAt(),
                $donation->getAmount(),
                $donation->getType(),
                $donation->isSubscription(),
                $donation->isMembership(),
                $donation->getStatus(),
                $donation->getUuid(),
                $donation->getDonator()
            );
        }

        $otherDonations = $this->donationRepository->findOfflineDonationsByAdherent($adherent);
        foreach ($otherDonations as $donation) {
            $history[] = $this->createDonationValueObject(
                $donation->getDonatedAt() ?? $donation->getCreatedAt(),
                $donation->getAmount(),
                $donation->getType(),
                $donation->isSubscription(),
                $donation->isMembership(),
                $donation->getStatus(),
                $donation->getUuid(),
                $donation->getDonator()
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
        bool $isMembership,
        string $status,
        UuidInterface $uuid,
        Donator $donator,
    ): DonationValueObject {
        return new DonationValueObject(
            $date,
            $amount,
            $type,
            $isSubscription,
            $isMembership,
            $status,
            $uuid,
            $donator->getId(),
            $donator->getIdentifier(),
            $donator->getFullName()
        );
    }
}

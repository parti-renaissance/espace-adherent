<?php

namespace App\Twig;

use App\Donation\DonationManager;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Repository\DonationRepository;
use App\Repository\TaxReceiptRepository;
use Twig\Extension\RuntimeExtensionInterface;

class AdherentAdminRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly DonationManager $donationManager,
        private readonly DonationRepository $donationRepository,
        private readonly TaxReceiptRepository $taxReceiptRepository,
    ) {
    }

    /**
     * @return Donation[]|array
     */
    public function getDonationsHistory(Adherent $adherent): array
    {
        return $this->donationManager->getHistory($adherent, false);
    }

    public function getTaxReceiptsForAdherent(Adherent $adherent): array
    {
        return $this->taxReceiptRepository->findAllByAdherent($adherent);
    }

    public function getTaxReceiptsForDonator(Donator $donator): array
    {
        return $this->taxReceiptRepository->findAllByDonator($donator);
    }

    /**
     * @return Donation[]|array
     */
    public function getSubscribedDonations(Adherent $adherent): array
    {
        return $this->donationRepository->findAllSubscribedDonationByEmail($adherent->getEmailAddress());
    }

    public function getLastSubscriptionEnded(Adherent $adherent): ?Donation
    {
        return $this->donationRepository->findLastSubscriptionEndedDonationByEmail($adherent->getEmailAddress());
    }
}

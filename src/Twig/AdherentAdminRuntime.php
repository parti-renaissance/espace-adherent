<?php

namespace App\Twig;

use App\Donation\DonationManager;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Repository\DonationRepository;
use Twig\Extension\RuntimeExtensionInterface;

class AdherentAdminRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly DonationManager $donationManager,
        private readonly DonationRepository $donationRepository,
    ) {
    }

    /**
     * @return Donation[]|array
     */
    public function getDonationsHistory(Adherent $adherent): array
    {
        return $this->donationManager->getHistory($adherent, false);
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

<?php

namespace App\Donation;

use App\Address\PostAddressFactory;
use App\Donation\Request\DonationRequest;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Ramsey\Uuid\Uuid;

class DonationFactory
{
    public function __construct(
        private readonly PostAddressFactory $addressFactory,
        private readonly DonationRequestUtils $donationRequestUtils,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public function createFromDonationRequest(
        DonationRequest $request,
        Donator $donator,
        bool $forReAdhesion = false,
    ): Donation {
        $donation = new Donation(
            $uuid = Uuid::uuid4(),
            $request->getType(),
            (int) $request->getAmount() * 100,
            new \DateTimeImmutable(),
            $this->addressFactory->createFromAddress($request->getAddress()),
            $request->getClientIp(),
            $request->getDuration(),
            $this->donationRequestUtils->buildDonationReference(
                $uuid,
                $request->getFirstName().' '.$request->getLastName()
            ),
            $request->getNationality(),
            $request->getCode(),
            $donator
        );

        $donation->setSource($request->getSource());

        if ($request->isLocalDestination()) {
            $donation->setZone($this->findZone($donation));
        }

        if ($forReAdhesion) {
            $donation->setReAdhesion(true);
        }

        if ($request->getDonatedAt()) {
            $donation->setDonatedAt($request->getDonatedAt());
        }

        $donation->utmSource = $request->utmSource;
        $donation->utmCampaign = $request->utmCampaign;

        return $donation;
    }

    public function duplicate(Donation $initialDonation): Donation
    {
        return clone $initialDonation;
    }

    private function findZone(Donation $donation): ?Zone
    {
        return $this->zoneRepository->findOneDepartmentByPostalCode($donation->getPostalCode());
    }
}

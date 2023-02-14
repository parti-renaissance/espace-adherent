<?php

namespace App\Donation;

use App\Address\PostAddressFactory;
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
        private readonly ZoneRepository $zoneRepository
    ) {
    }

    public function createFromDonationRequest(DonationRequest $request, Donator $donator): Donation
    {
        $donation = new Donation(
            $uuid = Uuid::uuid4(),
            $request->getType(),
            $request->getAmount() * 100,
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

        return $donation;
    }

    private function findZone(Donation $donation): ?Zone
    {
        return $this->zoneRepository->findOneDepartmentByPostalCode($donation->getPostalCode());
    }
}

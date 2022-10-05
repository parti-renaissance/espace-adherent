<?php

namespace App\Donation;

use App\Address\PostAddressFactory;
use App\Entity\Donation;
use App\Entity\Donator;
use Ramsey\Uuid\Uuid;

class DonationFactory
{
    private $addressFactory;
    private $donationRequestUtils;

    public function __construct(PostAddressFactory $addressFactory, DonationRequestUtils $donationRequestUtils)
    {
        $this->addressFactory = $addressFactory;
        $this->donationRequestUtils = $donationRequestUtils;
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

        return $donation;
    }
}

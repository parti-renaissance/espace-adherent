<?php

namespace AppBundle\Donation;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;

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
        return new Donation(
            $request->getUuid(),
            $request->getType(),
            $request->getAmount() * 100,
            $this->addressFactory->createFlexible(
                $request->getCountry(),
                $request->getPostalCode(),
                $request->getCityName(),
                $request->getAddress()
            ),
            $request->getClientIp(),
            $request->getDuration(),
            $this->donationRequestUtils->buildDonationReference(
                $request->getUuid(),
                $request->getFirstName().' '.$request->getLastName()
            ),
            $request->getNationality(),
            $donator
        );
    }
}

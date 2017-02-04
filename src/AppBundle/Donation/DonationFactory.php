<?php

namespace AppBundle\Donation;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\Donation;

class DonationFactory
{
    private $addressFactory;

    public function __construct(PostAddressFactory $addressFactory = null)
    {
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromDonationRequest(DonationRequest $request): Donation
    {
        return new Donation(
            $request->getAmount() * 100,
            $request->getGender(),
            $request->getFirstName(),
            $request->getLastName(),
            $request->getEmailAddress(),
            $this->addressFactory->createFromAddress($request->getAddress()),
            $request->getPhone()
        );
    }
}

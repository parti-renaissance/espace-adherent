<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donator;

class DonatorFactory
{
    public function createFromDonationRequest(DonationRequest $request): Donator
    {
        return new Donator(
            $request->getLastName(),
            $request->getFirstName(),
            $request->getCityName(),
            $request->getCountry(),
            $request->getEmailAddress(),
            $request->getGender(),
            $request->getNationality()
        );
    }
}

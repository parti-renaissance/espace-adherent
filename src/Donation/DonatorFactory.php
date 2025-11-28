<?php

declare(strict_types=1);

namespace App\Donation;

use App\Donation\Request\DonationRequest;
use App\Entity\Donator;

class DonatorFactory
{
    public function createFromDonationRequest(DonationRequest $request): Donator
    {
        return new Donator(
            $request->getLastName(),
            $request->getFirstName(),
            $request->getAddress()->getCityName(),
            $request->getAddress()->getCountry(),
            $request->getEmailAddress(),
            $request->getGender()
        );
    }
}

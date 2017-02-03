<?php

namespace AppBundle\Membership\OnBoarding;

use AppBundle\Donation\DonationRequest;
use AppBundle\Membership\MembershipOnBoardingInterface;

/**
 * A simple instance to store a donation while on boarding.
 */
final class RegisteringDonation implements MembershipOnBoardingInterface
{
    private $donationRequest;

    public function __construct(DonationRequest $donationRequest)
    {
        $this->donationRequest = $donationRequest;
    }

    public function getDonationRequest(): DonationRequest
    {
        return $this->donationRequest;
    }
}

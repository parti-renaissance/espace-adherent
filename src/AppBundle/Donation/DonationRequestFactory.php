<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class DonationRequestFactory
{
    public function createFromRequest(Request $request, float $amount, int $duration, $currentUser = null): DonationRequest
    {
        $clientIp = $request->getClientIp();

        if ($currentUser instanceof Adherent) {
            $donation = $this->createFromAdherent($currentUser, $clientIp, $amount, $duration);
        } else {
            $donation = new DonationRequest(Uuid::uuid4(), $clientIp, $amount, $duration);
        }

        if (($gender = $request->query->get('ge')) && in_array($gender, ['male', 'female'], true)) {
            $donation->setGender($gender);
        }

        if ($lastName = $request->query->get('ln')) {
            $donation->setLastName($lastName);
        }

        if ($firstName = $request->query->get('fn')) {
            $donation->setFirstName($firstName);
        }

        if ($email = $request->query->get('em')) {
            $donation->setEmailAddress(urldecode($email));
        }

        if ($country = $request->query->get('co')) {
            $donation->setCountry($country);
        }

        if ($postalCode = $request->query->get('pc')) {
            $donation->setPostalCode($postalCode);
        }

        if ($city = $request->query->get('ci')) {
            $donation->setCityName($city);
        }

        if ($cityName = $request->query->get('cn')) {
            $donation->setCityName($cityName);
        }

        if ($address = $request->query->get('ad')) {
            $donation->setAddress(urldecode($address));
        }

        if (($phoneCode = $request->query->get('phc')) && ($phoneNumber = $request->query->get('phn'))) {
            $donation->setPhone($this->createPhoneNumber($phoneCode, $phoneNumber));
        }

        return $donation;
    }

    public function createFromAdherent(
        Adherent $adherent,
        string $clientIp,
        int $defaultAmount = DonationRequest::DEFAULT_AMOUNT,
        int $duration
    ): DonationRequest {
        return DonationRequest::createFromAdherent($adherent, $clientIp, $defaultAmount, $duration);
    }

    private function createPhoneNumber(string $phoneCode, string $nationalNumber): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($phoneCode);
        $phone->setNationalNumber($nationalNumber);

        return $phone;
    }
}

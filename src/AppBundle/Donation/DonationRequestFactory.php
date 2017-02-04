<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use libphonenumber\PhoneNumber;
use Symfony\Component\HttpFoundation\Request;

class DonationRequestFactory
{
    public function createFromRequest(Request $request, float $defaultAmount = 50.0): DonationRequest
    {
        $donation = new DonationRequest((float) $request->query->get('montant', $defaultAmount));

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
            $donation->getAddress()->setCountry($country);
        }

        if ($postalCode = $request->query->get('pc')) {
            $donation->getAddress()->setPostalCode($postalCode);
        }

        if ($city = $request->query->get('ci')) {
            $donation->getAddress()->setCity($city);
        }

        if ($cityName = $request->query->get('cn')) {
            $donation->getAddress()->setCityName($cityName);
        }

        if ($address = $request->query->get('ad')) {
            $donation->getAddress()->setAddress(urldecode($address));
        }

        if (($phoneCode = $request->query->get('phc')) && ($phoneNumber = $request->query->get('phn'))) {
            $phone = new PhoneNumber();
            $phone->setCountryCode($phoneCode);
            $phone->setNationalNumber($phoneNumber);

            $donation->setPhone($phone);
        }

        return $donation;
    }

    public function createFromAdherent(Adherent $adherent, int $defaultAmount = 50): DonationRequest
    {
        $donation = DonationRequest::createFromAdherent($adherent);
        $donation->setAmount($defaultAmount);

        return $donation;
    }
}

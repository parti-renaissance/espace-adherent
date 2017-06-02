<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use libphonenumber\PhoneNumber;

class ProcurationFactory
{
    public function createRequestFromArray(array $data): ProcurationRequest
    {
        $phone = null;
        if (isset($data['phone'])) {
            $phone = $this->createPhone($data['phone']);
        }

        $request = new ProcurationRequest();
        $request->setGender($data['gender']);
        $request->setFirstNames($data['first_names']);
        $request->setLastName($data['last_name']);
        $request->setEmailAddress($data['email']);
        $request->setAddress($data['address']);
        $request->setPostalCode($data['postalCode'] ?? null);
        $request->setCity($data['city'] ?? null);
        $request->setCityName($data['cityName'] ?? null);
        $request->setPhone($phone);
        $request->setBirthdate($this->createBirthdate($data['birthdate']));
        $request->setVoteCountry($data['voteCountry']);
        $request->setVotePostalCode($data['votePostalCode'] ?? null);
        $request->setVoteCity($data['voteCity'] ?? null);
        $request->setVoteCityName($data['voteCityName'] ?? null);
        $request->setVoteOffice($data['voteOffice'] ?? null);
        $request->setElectionPresidentialFirstRound($data['electionPresidentialFirstRound'] ?? false);
        $request->setElectionPresidentialSecondRound($data['electionPresidentialSecondRound'] ?? false);
        $request->setElectionLegislativeFirstRound($data['electionLegislativeFirstRound'] ?? false);
        $request->setElectionLegislativeSecondRound($data['electionLegislativeSecondRound'] ?? false);
        $request->setReason($data['reason'] ?? ProcurationRequest::REASON_HOLIDAYS);

        return $request;
    }

    public function createProxyProposalFromArray(array $data): ProcurationProxy
    {
        $phone = null;
        if (isset($data['phone'])) {
            $phone = $this->createPhone($data['phone']);
        }

        $proxy = new ProcurationProxy($data['referent']);
        $proxy->setGender($data['gender']);
        $proxy->setFirstNames($data['first_names']);
        $proxy->setLastName($data['last_name']);
        $proxy->setEmailAddress($data['email']);
        $proxy->setAddress($data['address']);
        $proxy->setPostalCode($data['postalCode'] ?? null);
        $proxy->setCity($data['city'] ?? null);
        $proxy->setCityName($data['cityName'] ?? null);
        $proxy->setPhone($phone);
        $proxy->setBirthdate($this->createBirthdate($data['birthdate']));
        $proxy->setVoteCountry($data['voteCountry']);
        $proxy->setVotePostalCode($data['votePostalCode'] ?? null);
        $proxy->setVoteCity($data['voteCity'] ?? null);
        $proxy->setVoteCityName($data['voteCityName'] ?? null);
        $proxy->setVoteOffice($data['voteOffice'] ?? null);
        $proxy->setElectionPresidentialFirstRound($data['electionPresidentialFirstRound'] ?? false);
        $proxy->setElectionPresidentialSecondRound($data['electionPresidentialSecondRound'] ?? false);
        $proxy->setElectionLegislativeFirstRound($data['electionLegislativeFirstRound'] ?? false);
        $proxy->setElectionLegislativeSecondRound($data['electionLegislativeSecondRound'] ?? false);
        $proxy->setReliability($data['reliability'] ?? 0);
        $proxy->setReliabilityDescription($data['reliabilityDescription'] ?? '');

        return $proxy;
    }

    /**
     * Returns a PhoneNumber object.
     *
     * The format must be something like "33 0102030405"
     *
     * @param string $phoneNumber
     *
     * @return PhoneNumber
     */
    private function createPhone($phoneNumber): PhoneNumber
    {
        list($country, $number) = explode(' ', $phoneNumber);

        $phone = new PhoneNumber();
        $phone->setCountryCode($country);
        $phone->setNationalNumber($number);

        return $phone;
    }

    /**
     * @param int|string|\DateTime $birthdate Valid date reprensentation
     *
     * @return \DateTime
     */
    private function createBirthdate($birthdate): \DateTime
    {
        if ($birthdate instanceof \DateTime) {
            return $birthdate;
        }

        return new \DateTime($birthdate);
    }
}

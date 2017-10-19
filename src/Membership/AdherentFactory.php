<?php

namespace AppBundle\Membership;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\Adherent;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdherentFactory
{
    private $encoders;
    private $addressFactory;

    public function __construct(
        EncoderFactoryInterface $encoders,
        PostAddressFactory $addressFactory = null
    ) {
        $this->encoders = $encoders;
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromMembershipRequest(MembershipRequest $request): Adherent
    {
        return new Adherent(
            Adherent::createUuid($request->getEmailAddress()),
            $request->getEmailAddress(),
            $request->gender,
            $request->firstName,
            $request->lastName,
            clone $request->getBirthdate(),
            $request->position,
            $this->addressFactory->createFromAddress($request->getAddress()),
            $request->getPhone(),
            Adherent::ENABLED,
            'now',
            $request->comEmail,
            $request->comMobile
        );
    }

    public function createFromArray(array $data): Adherent
    {
        $phone = null;
        if (isset($data['phone'])) {
            $phone = $this->createPhone($data['phone']);
        }

        return new Adherent(
            isset($data['uuid']) ? Uuid::fromString($data['uuid']) : Adherent::createUuid($data['email']),
            $data['email'],
            $data['gender'],
            $data['first_name'],
            $data['last_name'],
            $this->createBirthdate($data['birthdate']),
            isset($data['position']) ? $data['position'] : ActivityPositions::EMPLOYED,
            $data['address'],
            $phone,
            Adherent::DISABLED,
            isset($data['registered_at']) ? $data['registered_at'] : 'now',
            false,
            false,
            isset($data['password']) ? $this->encodePassword($data['password']) : null
        );
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

    private function encodePassword(string $password): string
    {
        $encoder = $this->encoders->getEncoder(Adherent::class);

        return $encoder->encodePassword($password, null);
    }
}

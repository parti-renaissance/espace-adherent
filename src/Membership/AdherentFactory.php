<?php

namespace App\Membership;

use App\Address\PostAddressFactory;
use App\Entity\Adherent;
use App\Membership\MembershipRequest\CoalitionMembershipRequest;
use App\Membership\MembershipRequest\JeMengageMembershipRequest;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Membership\MembershipRequest\PlatformMembershipRequest;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdherentFactory
{
    private $encoders;
    private $addressFactory;

    public function __construct(EncoderFactoryInterface $encoders, PostAddressFactory $addressFactory = null)
    {
        $this->encoders = $encoders;
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromMembershipRequest(MembershipInterface $membershipRequest): Adherent
    {
        if ($membershipRequest instanceof CoalitionMembershipRequest) {
            return $this->createFromCoalitionMembershipRequest($membershipRequest);
        }

        if ($membershipRequest instanceof JeMengageMembershipRequest) {
            return $this->createFromJeMengageMembershipRequest($membershipRequest);
        }

        if ($membershipRequest instanceof PlatformMembershipRequest) {
            return $this->createFromPlatformMembershipRequest($membershipRequest);
        }

        throw new \LogicException(sprintf('Missing Adherent factory for membership request "%s"', \get_class($membershipRequest)));
    }

    public function createFromCoalitionMembershipRequest(CoalitionMembershipRequest $request): Adherent
    {
        return Adherent::createLight(
            Adherent::createUuid($request->getEmailAddress()),
            $request->getEmailAddress(),
            $request->firstName,
            $this->addressFactory->createFromZone($request->zone),
            $this->encodePassword(Uuid::uuid4()),
            Adherent::DISABLED,
            $request->getSource(),
            $request->coalitionSubscription,
            $request->causeSubscription
        );
    }

    public function createFromJeMengageMembershipRequest(JeMengageMembershipRequest $request): Adherent
    {
        $adherent = Adherent::create(
            Adherent::createUuid($request->getEmailAddress()),
            $request->getEmailAddress(),
            $this->encodePassword(Uuid::uuid4()),
            $request->gender,
            $request->firstName,
            $request->lastName,
            $request->birthdate,
            null,
            $this->addressFactory->createFromAddress($request->address),
            $request->phone
        );
        $adherent->setNationality($request->nationality);
        $adherent->setSource($request->getSource());

        return $adherent;
    }

    public function createFromPlatformMembershipRequest(PlatformMembershipRequest $request): Adherent
    {
        $adherent = Adherent::create(
            Adherent::createUuid($request->getEmailAddress()),
            $request->getEmailAddress(),
            $this->encodePassword($request->password),
            $request->gender,
            $request->firstName,
            $request->lastName,
            $request->getBirthdate() ? clone $request->getBirthdate() : null,
            $request->position,
            $this->addressFactory->createFromAddress($request->getAddress()),
            $request->getPhone(),
            null,
            false,
            Adherent::DISABLED,
            'now',
            [],
            [],
            $request->getMandates(),
            $request->nationality,
            $request->customGender
        );

        if (!$request->isAsUser()) {
            $adherent->join();
        }

        return $adherent;
    }

    public function createFromArray(array $data): Adherent
    {
        $phone = null;
        if (isset($data['phone'])) {
            $phone = PhoneNumberUtils::create($data['phone']);
        }

        $adherent = Adherent::create(
            isset($data['uuid']) ? Uuid::fromString($data['uuid']) : Adherent::createUuid($data['email']),
            $data['email'],
            $this->encodePassword($data['password']),
            $data['gender'] ?? null,
            $data['first_name'],
            $data['last_name'],
            isset($data['birthdate']) ? $this->createBirthdate($data['birthdate']) : null,
            isset($data['position']) ? $data['position'] : ActivityPositionsEnum::EMPLOYED,
            $data['address'],
            $phone,
            $data['nickname'] ?? null,
            $data['nickname_used'] ?? false,
            Adherent::DISABLED,
            isset($data['registered_at']) ? $data['registered_at'] : 'now',
            [],
            [],
            null,
            $data['nationality'] ?? null,
            $data['custom_gender'] ?? null
        );

        if (!isset($data['isAdherent']) || $data['isAdherent']) {
            $adherent->join();
        }

        return $adherent;
    }

    /**
     * @param int|string|\DateTime $birthdate Valid date representation
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

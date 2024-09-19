<?php

namespace App\Membership;

use App\Address\PostAddressFactory;
use App\Adherent\Tag\TagEnum;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Request\MembershipRequest;
use App\BesoinDEurope\Inscription\InscriptionRequest;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Membership\MembershipRequest\AvecVousMembershipRequest;
use App\Membership\MembershipRequest\JeMengageMembershipRequest;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Renaissance\Membership\Admin\AdherentCreateCommand;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdherentFactory
{
    private $encoders;
    private $addressFactory;

    public function __construct(EncoderFactoryInterface $encoders, ?PostAddressFactory $addressFactory = null)
    {
        $this->encoders = $encoders;
        $this->addressFactory = $addressFactory ?: new PostAddressFactory();
    }

    public function createFromMembershipRequest(MembershipInterface $membershipRequest): Adherent
    {
        if ($membershipRequest instanceof JeMengageMembershipRequest) {
            return $this->createFromJeMengageMembershipRequest($membershipRequest);
        }

        return $this->createFromAvecVousMembershipRequest($membershipRequest);
    }

    private function createFromAvecVousMembershipRequest(AvecVousMembershipRequest $request): Adherent
    {
        $adherent = Adherent::create(
            Adherent::createUuid($request->getEmailAddress()),
            $request->getEmailAddress(),
            $this->encodePassword(Uuid::uuid4()),
            null,
            $request->firstName,
            $request->lastName,
            $request->birthdate,
            null,
            $this->addressFactory->createFromAddress($request->address),
            $request->phone
        );
        $adherent->setSource($request->getSource());
        $adherent->setPapUserRole(true);

        return $adherent;
    }

    private function createFromJeMengageMembershipRequest(JeMengageMembershipRequest $request): Adherent
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
        $adherent->setPapUserRole(true);

        return $adherent;
    }

    public function createFromRenaissanceMembershipRequest(MembershipRequest $membershipRequest): Adherent
    {
        $adherent = Adherent::create(
            uuid: Uuid::uuid4(),
            emailAddress: $membershipRequest->email,
            password: null,
            gender: $membershipRequest->civility,
            firstName: $membershipRequest->firstName,
            lastName: $membershipRequest->lastName,
            status: Adherent::ENABLED,
            nationality: $membershipRequest->nationality,
        );

        $adherent->tags = [TagEnum::SYMPATHISANT_ADHESION_INCOMPLETE];
        $adherent->setPapUserRole(true);

        return $adherent;
    }

    public function createFromBesoinDEuropeMembershipRequest(InscriptionRequest $inscriptionRequest): Adherent
    {
        $adherent = Adherent::create(
            uuid: Uuid::uuid4(),
            emailAddress: $inscriptionRequest->email,
            password: null,
            gender: $inscriptionRequest->civility,
            firstName: $inscriptionRequest->firstName,
            lastName: $inscriptionRequest->lastName,
            postAddress: PostAddressFactory::createFromAddress($inscriptionRequest->address),
            status: Adherent::ENABLED
        );

        $adherent->tags = [TagEnum::SYMPATHISANT_ENSEMBLE2024];
        $adherent->setPapUserRole(true);
        $adherent->join();
        $adherent->setV2(true);
        $adherent->finishAdhesionStep(AdhesionStepEnum::MAIN_INFORMATION);
        $adherent->setSource(MembershipSourceEnum::LEGISLATIVE);

        $adherent->utmSource = $inscriptionRequest->utmSource;
        $adherent->utmCampaign = $inscriptionRequest->utmCampaign;

        return $adherent;
    }

    public function createFromAdminAdherentCreateCommand(
        AdherentCreateCommand $command,
        Administrator $administrator,
    ): Adherent {
        $adherent = Adherent::createBlank(
            $command->gender,
            $command->firstName,
            $command->lastName,
            $command->nationality,
            $this->addressFactory->createFromAddress($command->address),
            $command->email,
            $command->phone,
            $command->birthdate,
            $command->cotisationDate,
            $command->partyMembership
        );

        $adherent->setSource($command->source);
        $adherent->setCreatedByAdministrator($administrator);

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
            $data['position'] ?? ActivityPositionsEnum::EMPLOYED,
            $data['address'],
            $phone,
            $data['nickname'] ?? null,
            $data['nickname_used'] ?? false,
            Adherent::DISABLED,
            $data['registered_at'] ?? 'now',
            null,
            $data['nationality'] ?? null,
            $data['custom_gender'] ?? null
        );

        if (!isset($data['is_adherent']) || $data['is_adherent']) {
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

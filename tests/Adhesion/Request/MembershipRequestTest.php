<?php

declare(strict_types=1);

namespace Tests\App\Adhesion\Request;

use App\Adhesion\Request\MembershipRequest;
use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Utils\PhoneNumberUtils;
use App\ValueObject\Genders;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MembershipRequestTest extends KernelTestCase
{
    public function testCreateFromAdherentCopiesPhone(): void
    {
        $phone = PhoneNumberUtils::create('+33611223344');

        $adherent = Adherent::createBlank(
            publicId: 'AB1234',
            gender: Genders::MALE,
            firstName: 'Louis',
            lastName: 'Roche',
            nationality: 'FR',
            postAddress: PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
            email: 'louis@example.org',
            phone: $phone,
            birthdate: null,
        );

        $request = MembershipRequest::createFromAdherent($adherent);

        self::assertSame($phone, $request->phone);
    }

    public function testPhoneIsRequiredInAdhesionGroup(): void
    {
        $request = new MembershipRequest();
        $request->phone = null;

        $violations = $this->getValidator()->validateProperty($request, 'phone', ['adhesion']);

        self::assertCount(1, $violations);
        self::assertSame('common.phone_number.required', $violations->get(0)->getMessageTemplate());
    }

    public function testPhoneWithValidNumberPassesAdhesionGroup(): void
    {
        $request = new MembershipRequest();
        $request->phone = PhoneNumberUtils::create('+33611223344');

        $violations = $this->getValidator()->validateProperty($request, 'phone', ['adhesion']);

        self::assertCount(0, $violations);
    }

    public function testPhoneIsNotRequiredInDefaultGroup(): void
    {
        $request = new MembershipRequest();
        $request->phone = null;

        $violations = $this->getValidator()->validateProperty($request, 'phone', ['Default']);

        self::assertCount(0, $violations);
    }

    private function getValidator(): ValidatorInterface
    {
        return self::getContainer()->get(ValidatorInterface::class);
    }
}

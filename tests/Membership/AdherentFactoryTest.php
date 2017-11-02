<?php

namespace Tests\AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Membership\AdherentFactory;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

class AdherentFactoryTest extends TestCase
{
    public function testCreateNonFrenchAdherentFromArray()
    {
        $factory = $this->createFactory();

        $adherent = $factory->createFromArray([
            'password' => 'secret!12345',
            'email' => 'michelle.dufour@example.ch',
            'gender' => 'female',
            'first_name' => 'Michelle',
            'last_name' => 'Dufour',
            'address' => PostAddress::createForeignAddress('CH', '1206', 'Geneva', "39 Rue de l'Athénée"),
            'birthdate' => '1972-11-23',
        ]);

        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertNull($adherent->getPhone());
        $this->assertNull($adherent->getSalt());
        $this->assertSame(['ROLE_USER', 'ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertSame('michelle.dufour@example.ch', $adherent->getUsername());
        $this->assertSame('female', $adherent->getGender());
        $this->assertSame('Michelle', $adherent->getFirstName());
        $this->assertSame('Dufour', $adherent->getLastName());
        $this->assertSame('CH', $adherent->getCountry());
        $this->assertSame("39 Rue de l'Athénée", $adherent->getAddress());
        $this->assertSame('1206', $adherent->getPostalCode());
        $this->assertNull($adherent->getCity());
        $this->assertSame('Geneva', $adherent->getCityName());
        $this->assertEquals(new \DateTime('1972-11-23'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::EMPLOYED, $adherent->getPosition());
    }

    public function testCreateFrenchAdherentFromArray()
    {
        $factory = $this->createFactory();

        $adherent = $factory->createFromArray([
            'password' => '12345!secret',
            'email' => 'carl999@example.fr',
            'gender' => 'male',
            'first_name' => 'Carl',
            'last_name' => 'Mirabeau',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182'),
            'birthdate' => '1950-07-08',
            'position' => ActivityPositions::RETIRED,
            'phone' => '33 0102030405',
        ]);

        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertInstanceOf(PhoneNumber::class, $adherent->getPhone());
        $this->assertNull($adherent->getSalt());
        $this->assertSame(['ROLE_USER', 'ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertSame('carl999@example.fr', $adherent->getUsername());
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('Carl', $adherent->getFirstName());
        $this->assertSame('Mirabeau', $adherent->getLastName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertSame('122 rue de Mouxy', $adherent->getAddress());
        $this->assertSame('73100', $adherent->getPostalCode());
        $this->assertSame('73100-73182', $adherent->getCity());
        $this->assertEquals(new \DateTime('1950-07-08'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::RETIRED, $adherent->getPosition());
    }

    public function testCreateFrenchAdherentFromAPIResponse()
    {
        $data = [
            'uuid' => '79b67fa4-b25d-45dd-b6dc-bf73f2b53fa2',
            'emailAddress' => 'carl999@example.fr',
            'firstName' => 'Carl',
            'lastName' => 'Mirabeau',
            'zipCode' => '73100',
        ];

        $factory = $this->createFactory();
        $adherent = $factory->createFromAPIResponse($data);

        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertNull($adherent->getPhone());
        $this->assertNull($adherent->getSalt());
        $this->assertNull($adherent->getPassword());
        $this->assertNull($adherent->getGender());
        $this->assertNull($adherent->getCountry());
        $this->assertNull($adherent->getAddress());
        $this->assertNull($adherent->getCity());
        $this->assertNull($adherent->getBirthdate());
        $this->assertNull($adherent->getPosition());
        $this->assertNull($adherent->getGender());
        $this->assertSame(['ROLE_USER', 'ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertSame('carl999@example.fr', $adherent->getUsername());
        $this->assertSame('Carl', $adherent->getFirstName());
        $this->assertSame('Mirabeau', $adherent->getLastName());
        $this->assertSame('73100', $adherent->getPostalCode());
    }

    private function createFactory()
    {
        return new AdherentFactory(
            new EncoderFactory([
                Adherent::class => new PlaintextPasswordEncoder(),
            ])
        );
    }
}

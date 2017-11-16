<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\NullablePostAddress;
use AppBundle\Entity\PostAddress;
use AppBundle\CitizenProject\CitizenProjectCreationCommand;
use AppBundle\CitizenProject\CitizenProjectFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;

class CitizenProjectFactoryTest extends TestCase
{
    public function testCreateCitizenProjectFromCitizenProjectCreationCommand()
    {
        $email = 'jean.dupont@example.com';
        $uuid = Adherent::createUuid($email);
        $name = 'Projet citoyen à Lyon 1er Lyon 1er';
        $description = 'le projet citoyen à Lyon 1er';
        $address = NullableAddress::createFromAddress(NullablePostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'));

        $adherent = new Adherent(
            $uuid,
            $email,
            'password',
            'male',
            'Jean',
            'DUPONT',
            new \DateTime('1991-02-09'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );

        $command = CitizenProjectCreationCommand::createFromAdherent($adherent);
        $command->setAddress($address);
        $command->setPhone((new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080901'));
        $command->name = $name;
        $command->description = $description;

        $citizenProjectFactory = new CitizenProjectFactory();
        $citizenProject = $citizenProjectFactory->createFromCitizenProjectCreationCommand($command);

        $this->assertInstanceOf(CitizenProject::class, $citizenProject);
        $this->assertSame($address->getAddress(), $citizenProject->getAddress());
        $this->assertSame($name, $citizenProject->getName());
        $this->assertSame($description, $citizenProject->getDescription());
        $this->assertSame($adherent->getUuid()->toString(), $citizenProject->getCreatedBy());
    }
}

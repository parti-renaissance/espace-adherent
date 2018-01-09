<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Address\Address;
use AppBundle\Committee\CommitteeCreationCommand;
use AppBundle\Committee\CommitteeFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\PostAddress;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;

class CommitteeFactoryTest extends TestCase
{
    public function testCreateCommitteeFromCommitteeCreationCommand()
    {
        $email = 'm.dupont@example.com';
        $uuid = Adherent::createUuid($email);
        $name = 'En Marche ! de Lyon 1er';
        $description = 'Le comité En Marche ! de Lyon 1er';
        $facebook = 'https://facebook.com/en-marche';
        $twitter = 'enMarcheLyon';
        $googlePlus = 'https://googleplus.com/en-marche';
        $address = Address::createFromAddress(PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'));

        $adherent = new Adherent(
            $uuid,
            $email,
            'male',
            'Damien',
            'DUPONT',
            new \DateTime('1979-03-25'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );

        $command = CommitteeCreationCommand::createFromAdherent($adherent);
        $command->setAddress($address);
        $command->setPhone((new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'));
        $command->name = $name;
        $command->description = $description;
        $command->facebookPageUrl = $facebook;
        $command->twitterNickname = $twitter;
        $command->googlePlusPageUrl = $googlePlus;

        $committeeFactory = new CommitteeFactory();
        $committee = $committeeFactory->createFromCommitteeCreationCommand($command);

        $this->assertInstanceOf(Committee::class, $committee);
        $this->assertSame($address->getAddress(), $committee->getAddress());
        $this->assertSame($name, $committee->getName());
        $this->assertSame($description, $committee->getDescription());
        $this->assertSame($adherent->getUuid()->toString(), $committee->getCreatedBy());
        $this->assertSame($facebook, $committee->facebookPageUrl);
        $this->assertSame($twitter, $committee->twitterNickname);
        $this->assertSame($googlePlus, $committee->googlePlusPageUrl);
    }
}

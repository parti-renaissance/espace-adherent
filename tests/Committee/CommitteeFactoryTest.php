<?php

namespace Tests\App\Committee;

use App\Address\Address;
use App\Committee\CommitteeCreationCommand;
use App\Committee\CommitteeFactory;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\PostAddress;
use App\Referent\ReferentTagManager;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;

/**
 * @group committee
 */
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
        $address = Address::createFromAddress(PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'));

        $adherent = Adherent::create(
            $uuid,
            $email,
            'password',
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

        $referentTagManager = $this->createMock(ReferentTagManager::class);
        $referentTagManager
            ->expects(self::never())
            ->method('assignReferentLocalTags')
        ;

        $committeeFactory = new CommitteeFactory($referentTagManager);
        $committee = $committeeFactory->createFromCommitteeCreationCommand($command);

        $this->assertInstanceOf(Committee::class, $committee);
        $this->assertSame($address->getAddress(), $committee->getAddress());
        $this->assertSame($name, $committee->getName());
        $this->assertSame($description, $committee->getDescription());
        $this->assertSame($adherent->getUuid()->toString(), $committee->getCreatedBy());
        $this->assertSame($facebook, $committee->facebookPageUrl);
        $this->assertSame($twitter, $committee->twitterNickname);
    }
}

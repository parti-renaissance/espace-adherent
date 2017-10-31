<?php

namespace Tests\AppBundle\Group;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\NullablePostAddress;
use AppBundle\Entity\PostAddress;
use AppBundle\Group\GroupCreationCommand;
use AppBundle\Group\GroupFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;

class GroupFactoryTest extends TestCase
{
    public function testCreateGroupFromGroupCreationCommand()
    {
        $email = 'jean.dupont@example.com';
        $uuid = Adherent::createUuid($email);
        $name = 'MOOC à Lyon 1er Lyon 1er';
        $description = 'L\équipe MOOC à  Lyon 1er';
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

        $command = GroupCreationCommand::createFromAdherent($adherent);
        $command->setAddress($address);
        $command->setPhone((new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080901'));
        $command->name = $name;
        $command->description = $description;

        $groupFactory = new GroupFactory();
        $group = $groupFactory->createFromGroupCreationCommand($command);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertSame($address->getAddress(), $group->getAddress());
        $this->assertSame($name, $group->getName());
        $this->assertSame($description, $group->getDescription());
        $this->assertSame($adherent->getUuid()->toString(), $group->getCreatedBy());
    }
}

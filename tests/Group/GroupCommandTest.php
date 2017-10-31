<?php

namespace Tests\AppBundle\Group;

use AppBundle\Entity\NullablePostAddress;
use AppBundle\Group\GroupCommand;
use AppBundle\Entity\Group;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GroupCommandTest extends TestCase
{
    const CREATOR_UUID = '0214e826-0116-4caa-a635-3f6f51a86750';

    public function testCreateGroupCommandFromGroup()
    {
        $name = 'MOOC à Lyon 1er';
        $description = 'L\équipe MOOC à Lyon 1er';
        $uuid = Group::createUuid($name);

        $group = new Group(
            $uuid,
            Uuid::fromString(self::CREATOR_UUID),
            $name,
            $description,
            NullablePostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'),
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69001-en-marche-lyon'
        );

        $groupCommand = GroupCommand::createFromGroup($group);

        $this->assertInstanceOf(GroupCommand::class, $groupCommand);
        $this->assertSame($uuid, $groupCommand->getGroupUuid());
        $this->assertSame($group, $groupCommand->getGroup());
        $this->assertSame($name, $groupCommand->name);
        $this->assertSame($description, $groupCommand->description);
    }
}

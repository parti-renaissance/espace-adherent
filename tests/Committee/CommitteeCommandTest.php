<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Committee\CommitteeCommand;
use AppBundle\Entity\Committee;
use AppBundle\Entity\PostAddress;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group committee
 */
class CommitteeCommandTest extends TestCase
{
    const CREATOR_UUID = '3966af25-2b09-407c-9283-c4d2103d0448';

    public function testCreateCommitteeCommandFromCommittee()
    {
        $name = 'En Marche ! de Lyon 1er';
        $description = 'Le comité En Marche ! de Lyon 1er';
        $uuid = Committee::createUuid($name);
        $facebook = 'https://facebook.com/en-marche';
        $twitter = 'enMarcheLyon';

        $committee = new Committee(
            $uuid,
            Uuid::fromString(self::CREATOR_UUID),
            $name,
            $description,
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'),
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69001-en-marche-lyon'
        );
        $committee->setSocialNetworks(
            $facebook,
            $twitter
        );

        $committeeCommand = CommitteeCommand::createFromCommittee($committee);

        $this->assertInstanceOf(CommitteeCommand::class, $committeeCommand);
        $this->assertSame($uuid, $committeeCommand->getCommitteeUuid());
        $this->assertSame($committee, $committeeCommand->getCommittee());
        $this->assertSame($name, $committeeCommand->name);
        $this->assertSame($description, $committeeCommand->description);
        $this->assertSame($facebook, $committeeCommand->facebookPageUrl);
        $this->assertSame($twitter, $committeeCommand->twitterNickname);
    }
}

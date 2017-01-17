<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Exception\CommitteeAlreadyApprovedException;
use Ramsey\Uuid\Uuid;

class CommitteeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $committee = $this->createCommittee();

        $this->assertInstanceOf(Uuid::class, $committee->getUuid());
        $this->assertSame('En Marche ! - Clichy 92', $committee->getName());
        $this->assertSame('92110-en-marche-clichy', $committee->getSlug());
        $this->assertSame('Le comité En Marche ! de Clichy village', $committee->getDescription());
        $this->assertSame('FR', $committee->getCountry());
        $this->assertSame('92110', $committee->getPostalCode());
        $this->assertSame('92110-92024', $committee->getCity());
        $this->assertNull($committee->getFacebookPageUrl());
        $this->assertNull($committee->getTwitterNickname());
        $this->assertNull($committee->getGooglePlusPageUrl());
        $this->assertFalse($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertNull($committee->getApprovedAt());
    }

    public function testCommitteeIsCreatedByAdherent()
    {
        $committee = $this->createCommittee();

        $this->assertTrue($committee->isCreatedBy(Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae')));
        $this->assertFalse($committee->isCreatedBy(Uuid::fromString('82a861b6-4287-4f44-8a84-145b4dac0781')));
    }

    public function testSetSocialNetworkPagesUrls()
    {
        $committee = $this->createCommittee();

        $committee->setSocialNetworks(
            'https://facebook.com/en-marche',
            '@EnMarcheClichy',
            'https://googleplus.com/en-marche'
        );

        $this->assertSame('https://facebook.com/en-marche', $committee->getFacebookPageUrl());
        $this->assertSame('EnMarcheClichy', $committee->getTwitterNickname());
        $this->assertSame('https://googleplus.com/en-marche', $committee->getGooglePlusPageUrl());

        $committee->setFacebookPageUrl('https://facebook.com/en-marche-avant');
        $committee->setTwitterNickname('EnMarcheClichy92');
        $committee->setGooglePlusPageUrl('https://googleplus.com/en-marche-avant');

        $this->assertSame('https://facebook.com/en-marche-avant', $committee->getFacebookPageUrl());
        $this->assertSame('EnMarcheClichy92', $committee->getTwitterNickname());
        $this->assertSame('https://googleplus.com/en-marche-avant', $committee->getGooglePlusPageUrl());
    }

    public function testApproveCommittee()
    {
        $committee = $this->createCommittee();
        $membership = $committee->approved('2016-01-18 21:23:12');

        $this->assertInstanceOf(CommitteeMembership::class, $membership);
        $this->assertTrue($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertFalse($committee->isWaitingForApproval());
        $this->assertEquals(new \DateTimeImmutable('2016-01-18 21:23:12'), $committee->getApprovedAt());
        $this->assertTrue($membership->isHostMember());
    }

    public function testApproveCommitteeTwice()
    {
        $committee = $this->createCommittee();
        $committee->approved();

        try {
            $committee->approved();
            $this->fail();
        } catch (CommitteeAlreadyApprovedException $exception) {
        }
    }

    private function createCommittee(): Committee
    {
        return new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Clichy 92',
            'Le comité En Marche ! de Clichy village',
            'FR',
            '92110',
            '92110-92024',
            '92110-en-marche-clichy'
        );
    }
}

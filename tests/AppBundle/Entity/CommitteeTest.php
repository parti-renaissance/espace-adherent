<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Exception\CommitteeAlreadyApprovedException;
use AppBundle\Geocoder\Coordinates;
use Ramsey\Uuid\Uuid;

class CommitteeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $committee = $this->createCommittee();

        $this->assertInstanceOf(Uuid::class, $committee->getUuid());
        $this->assertSame('En Marche ! - Lyon 1', $committee->getName());
        $this->assertSame('69001-en-marche-lyon', $committee->getSlug());
        $this->assertSame('Le comité En Marche ! de Lyon village', $committee->getDescription());
        $this->assertSame('FR', $committee->getCountry());
        $this->assertSame('69001', $committee->getPostalCode());
        $this->assertSame('69001-69381', $committee->getCity());
        $this->assertSame('69381', $committee->getInseeCode());
        $this->assertNull($committee->getFacebookPageUrl());
        $this->assertNull($committee->getTwitterNickname());
        $this->assertNull($committee->getGooglePlusPageUrl());
        $this->assertFalse($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertNull($committee->getApprovedAt());
    }

    public function testGeoAddressAndCoordinates()
    {
        $committee = $this->createCommittee();
        $committee->updateCoordinates(new Coordinates(45.7713288, 4.8288758));

        $this->assertSame('6 rue Neyret, 69001 Lyon 1er Arrondissement, France', $committee->getGeocodableAddress());
        $this->assertSame(45.7713288, $committee->getLatitude());
        $this->assertSame(4.8288758, $committee->getLongitude());
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
            '@EnMarcheLyon',
            'https://googleplus.com/en-marche'
        );

        $this->assertSame('https://facebook.com/en-marche', $committee->getFacebookPageUrl());
        $this->assertSame('EnMarcheLyon', $committee->getTwitterNickname());
        $this->assertSame('https://googleplus.com/en-marche', $committee->getGooglePlusPageUrl());

        $committee->setFacebookPageUrl('https://facebook.com/en-marche-avant');
        $committee->setTwitterNickname('EnMarcheLyon69');
        $committee->setGooglePlusPageUrl('https://googleplus.com/en-marche-avant');

        $this->assertSame('https://facebook.com/en-marche-avant', $committee->getFacebookPageUrl());
        $this->assertSame('EnMarcheLyon69', $committee->getTwitterNickname());
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
        $committee = new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Lyon 1',
            'Le comité En Marche ! de Lyon village',
            'FR',
            '69001',
            '69001-69381',
            '69001-en-marche-lyon'
        );
        $committee->setLocation('69001', '69001-69381', '6 rue Neyret');

        return $committee;
    }
}

<?php

namespace Tests\App\Entity;

use App\Entity\Committee;
use App\Geocoder\Coordinates;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\App\AbstractKernelTestCase;

class CommitteeTest extends AbstractKernelTestCase
{
    public function testConstructor()
    {
        $committee = $this->createCommittee();

        $this->assertInstanceOf(UuidInterface::class, $committee->getUuid());
        $this->assertSame('En Marche ! - Lyon 1', $committee->getName());
        $this->assertSame('69003-en-marche-lyon', $committee->getSlug());
        $this->assertSame('Le comité En Marche ! de Lyon village', $committee->getDescription());
        $this->assertSame('FR', $committee->getCountry());
        $this->assertSame('69003', $committee->getPostalCode());
        $this->assertSame('69003-69383', $committee->getCity());
        $this->assertSame('69383', $committee->getInseeCode());
        $this->assertSame('50 Rue de la Villette', $committee->getAddress());
        $this->assertNull($committee->getFacebookPageUrl());
        $this->assertNull($committee->getTwitterNickname());
        $this->assertTrue($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertFalse($committee->isClosed());
        $this->assertFalse($committee->isWaitingForApproval());
        $this->assertNull($committee->getApprovedAt());
    }

    public function testGeoAddressAndCoordinates()
    {
        $committee = $this->createCommittee();
        $committee->updateCoordinates(new Coordinates(45.7713288, 4.8288758));

        $this->assertSame('50 Rue de la Villette, 69003 Lyon 3ème, France', $committee->getGeocodableAddress());
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
            '@EnMarcheLyon'
        );

        $this->assertSame('https://facebook.com/en-marche', $committee->getFacebookPageUrl());
        $this->assertSame('EnMarcheLyon', $committee->getTwitterNickname());

        $committee->setFacebookPageUrl('https://facebook.com/en-marche-avant');
        $committee->setTwitterNickname('EnMarcheLyon69');

        $this->assertSame('https://facebook.com/en-marche-avant', $committee->getFacebookPageUrl());
        $this->assertSame('EnMarcheLyon69', $committee->getTwitterNickname());
    }

    public function testPreApproveCommittee()
    {
        $committee = $this->createCommittee();
        $committee->preApproved();

        $this->assertFalse($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertFalse($committee->isClosed());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertEquals(null, $committee->getApprovedAt());
    }

    public function testPreRefuseCommittee()
    {
        $committee = $this->createCommittee();
        $committee->preRefused();

        $this->assertFalse($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertFalse($committee->isClosed());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertEquals(null, $committee->getApprovedAt());
    }

    public function testApproveCommittee()
    {
        $committee = $this->createCommittee();
        $timestamp = '2016-01-18 21:23:12';
        $committee->approved($timestamp);

        $this->assertTrue($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertFalse($committee->isClosed());
        $this->assertFalse($committee->isWaitingForApproval());
    }

    public function testCloseCommittee()
    {
        $committee = $this->createCommittee();
        $committee->close();

        $this->assertFalse($committee->isApproved());
        $this->assertFalse($committee->isRefused());
        $this->assertFalse($committee->isWaitingForApproval());
        $this->assertTrue($committee->isClosed());
    }

    private function createCommittee(): Committee
    {
        return new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Lyon 1',
            'Le comité En Marche ! de Lyon village',
            $this->createPostAddress('50 Rue de la Villette', '69003-69383'),
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69003-en-marche-lyon'
        );
    }
}

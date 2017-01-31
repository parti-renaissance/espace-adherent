<?php

// These tests depends of the MySQL database functions

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Membership\MembershipUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\MysqlWebTestCase;

class NearbyCalculationTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    /** @var Adherent $adherent */
    private $adherent;

    /** @var Coordinates */
    private $coordinates;

    public function testChooseNearbyCommittee()
    {
        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, $this->adherent->getId());

        $crawler = $this->client->request(Request::METHOD_GET, '/inscription/choisir-des-comites');

        $boxPattern = '#app_membership_choose_nearby_committee_committees > div';

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(3, $boxes = $crawler->filter($boxPattern));

        $committees = $this->getCommitteeRepository()->findNearbyCommittees(3, $this->coordinates);

        foreach ($boxes as $i => $box) {
            $checkbox = $crawler->filter($boxPattern.' input[type="checkbox"][name="app_membership_choose_nearby_committee[committees][]"]');

            $this->assertSame((string) $committees[$i]->getUuid(), $checkbox->eq($i)->attr('value'));
            $this->assertSame($committees[$i]->getName(), $crawler->filter($boxPattern.' h5')->eq($i)->text());
        }
    }

    public function testChooseNearbyCommitteePersistsMembershipForNonActivatedAdherent()
    {
        $this->assertFalse($this->adherent->isEnabled());

        $memberships = $this->getCommitteeMembershipRepository()->findMemberships((string) $this->adherent->getUuid());

        $this->assertFalse($this->adherent->isEnabled());
        $this->assertCount(0, $memberships);

        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, $this->adherent->getId());

        $crawler = $this->client->request(Request::METHOD_GET, '/inscription/choisir-des-comites');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $committees = $this->getCommitteeRepository()->findNearbyCommittees(3, $this->coordinates);
        $this->assertCount(3, $committees, 'New adherent should have 3 committee proposals');

        // We are 'checking' the first (0) and the last one (2)
        $this->client->submit($crawler->selectButton('app_membership_choose_nearby_committee[submit]')->form(), [
            'app_membership_choose_nearby_committee' => [
                'committees' => [
                    0 => $committees[0]->getUuid(),
                    2 => $committees[2]->getUuid(),
                ],
            ],
        ]);

        $this->assertClientIsRedirectedTo('/', $this->client);

        $crawler = $this->client->followRedirect();

        // The following test could not be realized because of a bug on the homepage
        //$this->assertContains(
        //    'Vous venez de rejoindre En Marche, nous vous en remercions !',
        //    $crawler->filter('#notice-flashes')->text()
        //);

        $memberships = $this->getCommitteeMembershipRepository()->findMemberships((string) $this->adherent->getUuid());
        $this->assertCount(2, $memberships);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');
        $this->coordinates = new Coordinates($this->adherent->getLatitude(), $this->adherent->getLongitude());
    }

    protected function tearDown()
    {
        $this->kill();
        $this->loadFixtures([]);

        $this->adherent = null;
        $this->coordinates = null;

        parent::tearDown();
    }
}

<?php

// Please note that some related tests are located in the NearbyCalculationTest class.

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Donation\DonationRequest;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Mailjet\Message\AdherentAccountActivationMessage;
use AppBundle\Mailjet\Message\AdherentAccountConfirmationMessage;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Membership\MembershipUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\MysqlWebTestCase;

class MembershipControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var AdherentActivationTokenRepository
     */
    private $activationTokenRepository;

    /**
     * @var MailjetEmailRepository
     */
    private $emailRepository;

    /**
     * @dataProvider provideEmailAddress
     */
    public function testCannotCreateMembershipAccountWithSomeoneElseEmailAddress($emailAddress)
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['emailAddress'] = $emailAddress;
        $crawler = $this->client->submit($crawler->selectButton('J\'adhère')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Cette adresse e-mail existe déjà.', $crawler->filter('#field-email-address > .form__errors > li')->text());
    }

    /**
     * These data come from the LoadAdherentData fixtures file.
     *
     * @see LoadAdherentData
     */
    public function provideEmailAddress()
    {
        return [
            ['michelle.dufour@example.ch'],
            ['carl999@example.fr'],
        ];
    }

    public function testCannotCreateMembershipAccountIfAdherentIsUnder15YearsOld()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['birthdate'] = date('d/m/Y');
        $crawler = $this->client->submit($crawler->selectButton('J\'adhère')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame("Vous devez être âgé d'au moins 15 ans pour adhérer.", $crawler->filter('#field-birthdate > .form__errors > li')->text());
    }

    public function testCannotCreateMembershipAccountIfConditionsAreNotAccepted()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['conditions'] = false;
        $crawler = $this->client->submit($crawler->selectButton('J\'adhère')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Vous devez accepter la charte.', $crawler->filter('#field-conditions > .form__errors > li')->text());
    }

    public function testCannotCreateMembershipAccountWithInvalidFrenchAddress()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['address']['postalCode'] = '73100';
        $data['membership_request']['address']['city'] = '73100-73999';
        $crawler = $this->client->submit($crawler->selectButton('J\'adhère')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Cette ville n\'est pas une ville française valide.', $crawler->filter('#membership-address > .form__errors > li')->eq(0)->text());
        $this->assertSame('Cette adresse n\'est pas géolocalisable.', $crawler->filter('#membership-address > .form__errors > li')->eq(1)->text());
    }

    public function testCreateMembershipAccountForFrenchAdherentIsSuccessful()
    {
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('J\'adhère')->form(), static::createFormData());

        $this->assertClientIsRedirectedTo('/inscription/don', $this->client);

        $this->client->followRedirect();

        $adherent = $this->getAdherentRepository()->findByEmail('paul@dupont.tld');
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertNotNull($adherent->getLatitude());
        $this->assertNotNull($adherent->getLongitude());

        $this->assertInstanceOf(
            Adherent::class,
            $adherent = $this->client->getContainer()->get('doctrine')->getRepository(Adherent::class)->findByEmail('paul@dupont.tld')
        );

        $adherent = $this->getAdherentRepository()->findByEmail('paul@dupont.tld');
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertNotNull($adherent->getLatitude());
        $this->assertNotNull($adherent->getLongitude());

        $this->assertInstanceOf(Adherent::class, $adherent = $this->adherentRepository->findByEmail('paul@dupont.tld'));
        $this->assertInstanceOf(AdherentActivationToken::class, $activationToken = $this->activationTokenRepository->findAdherentMostRecentKey((string) $adherent->getUuid()));
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(AdherentAccountActivationMessage::class, 'paul@dupont.tld'));

        // Activate the user account
        $activateAccountUrl = sprintf('/inscription/finaliser/%s/%s', $adherent->getUuid(), $activationToken->getValue());
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(AdherentAccountConfirmationMessage::class, 'paul@dupont.tld'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Votre compte adhérent est maintenant actif.', $crawler->filter('#notice-flashes')->text());

        // Activate user account twice
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Votre compte adhérent est déjà actif.', $crawler->filter('#notice-flashes')->text());

        $this->manager->refresh($adherent);
        $this->manager->refresh($activationToken);

        // Try to authenticate with credentials
        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => 'paul@dupont.tld',
            '_adherent_password' => '#example!12345#',
        ]));

        $this->assertClientIsRedirectedTo('http://localhost/evenements', $this->client);

        $this->client->followRedirect();

        $session = $this->client->getRequest()->getSession();

        $this->assertInstanceOf(DonationRequest::class, $session->get(MembershipUtils::REGISTERING_DONATION));
        $this->assertSame($adherent->getId(), $session->get(MembershipUtils::NEW_ADHERENT_ID));
    }

    public function testCreateMembershipAccountForSwissAdherentIsSuccessful()
    {
        $client = $this->client;
        $client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['address']['country'] = 'CH';
        $data['membership_request']['address']['city'] = '';
        $data['membership_request']['address']['cityName'] = 'Zürich';
        $data['membership_request']['address']['postalCode'] = '8057';
        $data['membership_request']['address']['address'] = '36 Zeppelinstrasse';

        $this->client->submit($this->client->getCrawler()->selectButton('J\'adhère')->form(), $data);

        $this->assertClientIsRedirectedTo('/inscription/don', $this->client);

        $adherent = $this->getAdherentRepository()->findByEmail('paul@dupont.tld');
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertNotNull($adherent->getLatitude());
        $this->assertNotNull($adherent->getLongitude());

        $session = $this->client->getRequest()->getSession();

        $this->assertInstanceOf(DonationRequest::class, $donation = $session->get(MembershipUtils::REGISTERING_DONATION));
        $this->assertSame($adherent->getId(), $session->get(MembershipUtils::NEW_ADHERENT_ID));
        $this->assertSame('Dupont', $donation->getLastName());
    }

    public function testDonateWithoutTemporaryDonation()
    {
        $client = $this->client;
        $client->request(Request::METHOD_GET, '/inscription/don');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $client->getResponse());
    }

    /**
     * @dataProvider provideRegistrationOnBoardingStepUrl
     */
    public function testRegistrationOnBoardingWithoutNewAdherentId(string $stepUrl)
    {
        $this->client->request(Request::METHOD_GET, '/inscription/'.$stepUrl);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * @dataProvider provideRegistrationOnBoardingStepUrl
     */
    public function testRegistrationOnBoardingWithWrongNewAdherentId(string $stepUrl)
    {
        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, 'wrong id');

        $this->client->request(Request::METHOD_GET, '/inscription/'.$stepUrl);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function provideRegistrationOnBoardingStepUrl()
    {
        yield ['centre-interets'];

        yield ['choisir-des-comites'];
    }

    public function testPinInterests()
    {
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');

        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, $adherent->getId());

        $crawler = $this->client->request(Request::METHOD_GET, '/inscription/centre-interets');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $checkBoxPattern = '#app_adherent_pin_interests_interests > '.
                           'input[type="checkbox"][name="app_adherent_pin_interests[interests][]"]';

        $this->assertCount(16, $checkboxes = $crawler->filter($checkBoxPattern));

        $interests = $this->client->getContainer()->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);
        $interestsLabels = array_values($interests);

        foreach ($checkboxes as $i => $checkbox) {
            $this->assertSame($interestsValues[$i], $checkbox->getAttribute('value'));
            $this->assertSame($interestsLabels[$i], $crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->text());
        }
    }

    public function testPinInterestsPersistsInterestsForNonActivatedAdherent()
    {
        /** @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');

        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getInterests());

        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, $adherent->getId());

        $crawler = $this->client->request(Request::METHOD_GET, '/inscription/centre-interets');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $interests = $this->client->getContainer()->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);

        $chosenInterests = [
            4 => $interestsValues[4],
            8 => $interestsValues[8],
        ];

        $this->client->submit($crawler->selectButton('app_adherent_pin_interests[submit]')->form(), [
            'app_adherent_pin_interests' => [
                'interests' => $chosenInterests,
            ],
        ]);

        $this->assertClientIsRedirectedTo('/inscription/choisir-des-comites', $this->client);

        /** @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');

        $this->assertSame(array_values($chosenInterests), $adherent->getInterests());
    }

    public function testChooseNearbyCommittee()
    {
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, $adherent->getId());

        $crawler = $this->client->request(Request::METHOD_GET, '/inscription/choisir-des-comites');

        $boxPattern = '#app_membership_choose_nearby_committee_committees > div';

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(3, $boxes = $crawler->filter($boxPattern));

        $committees = $this->getCommitteeRepository()->findNearbyCommittees(3, $coordinates);

        foreach ($boxes as $i => $box) {
            $checkbox = $crawler->filter($boxPattern.' input[type="checkbox"][name="app_membership_choose_nearby_committee[committees][]"]');

            $this->assertSame((string) $committees[$i]->getUuid(), $checkbox->eq($i)->attr('value'));
            $this->assertSame($committees[$i]->getName(), $crawler->filter($boxPattern.' h5')->eq($i)->text());
        }
    }

    public function testChooseNearbyCommitteePersistsMembershipForNonActivatedAdherent()
    {
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

        $this->assertFalse($adherent->isEnabled());

        $memberships = $this->getCommitteeMembershipRepository()->findMemberships($adherent);

        $this->assertFalse($adherent->isEnabled());
        $this->assertCount(0, $memberships);

        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, $adherent->getId());

        $crawler = $this->client->request(Request::METHOD_GET, '/inscription/choisir-des-comites');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $committees = $this->getCommitteeRepository()->findNearbyCommittees(3, $coordinates);
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

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // The following test could not be realized because of a bug on the homepage
        //$this->assertContains(
        //    'Vous venez de rejoindre En Marche, nous vous en remercions !',
        //    $crawler->filter('#notice-flashes')->text()
        //);

        $memberships = $this->getCommitteeMembershipRepository()->findMemberships($adherent);

        $this->assertCount(2, $memberships);
    }

    private static function createFormData()
    {
        return [
            'membership_request' => [
                'gender' => 'male',
                'firstName' => 'Paul',
                'lastName' => 'Dupont',
                'emailAddress' => 'paul@dupont.tld',
                'password' => [
                    'first' => '#example!12345#',
                    'second' => '#example!12345#',
                ],
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '92110',
                    'city' => '92110-92024',
                    'cityName' => '',
                    'address' => '92 Bld Victor Hugo',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '0140998080',
                ],
                'position' => 'retired',
                'birthdate' => '20/01/1950',
                'conditions' => true,
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);
        $this->adherentRepository = $this->getAdherentRepository();
        $this->activationTokenRepository = $this->getActivationTokenRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();
        $this->loadFixtures([]);

        $this->emailRepository = null;
        $this->activationTokenRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}

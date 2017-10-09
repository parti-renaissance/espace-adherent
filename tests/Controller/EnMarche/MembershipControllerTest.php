<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
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
use Tests\AppBundle\Config;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 * @group membership
 */
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
        $this->assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.', $crawler->filter('#membership-address > .form__errors > li')->eq(1)->text());
    }

    /**
     * @group skip
     */
    public function testCreateMembershipAccountForFrenchAdherentIsSuccessful()
    {
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('J\'adhère')->form(), static::createFormData());

        $this->assertClientIsRedirectedTo('/inscription/don', $this->client);

        $this->client->followRedirect();

        $adherent = $this->getAdherentRepository()->findByEmail('paul@dupont.tld');
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('Paul', $adherent->getFirstName());
        $this->assertSame('Dupont', $adherent->getLastName());
        $this->assertSame('92 Bld Victor Hugo', $adherent->getAddress());
        $this->assertSame('Clichy', $adherent->getCityName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertSame('20-01-1950', $adherent->getBirthdate()->format('d-m-Y'));
        $this->assertTrue($adherent->getComMobile());
        $this->assertFalse($adherent->getComEmail());
        $this->assertNotNull($adherent->getLatitude());
        $this->assertNotNull($adherent->getLongitude());

        $this->assertInstanceOf(
            Adherent::class,
            $adherent = $this->client->getContainer()->get('doctrine')->getRepository(Adherent::class)->findByEmail('paul@dupont.tld')
        );

        $this->assertInstanceOf(AdherentActivationToken::class, $activationToken = $this->activationTokenRepository->findAdherentMostRecentKey((string) $adherent->getUuid()));
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(AdherentAccountActivationMessage::class, 'paul@dupont.tld'));

        $session = $this->client->getRequest()->getSession();

        $this->assertInstanceOf(DonationRequest::class, $session->get(MembershipUtils::REGISTERING_DONATION));
        $this->assertSame($adherent->getId(), $session->get(MembershipUtils::NEW_ADHERENT_ID));

        // Activate the user account
        $activateAccountUrl = sprintf('/inscription/finaliser/%s/%s', $adherent->getUuid(), $activationToken->getValue());
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(AdherentAccountConfirmationMessage::class, 'paul@dupont.tld'));
        $this->assertClientIsRedirectedTo('/evenements', $this->client);

        $crawler = $this->client->followRedirect();

        // User is automatically logged-in and redirected to the events page
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Votre compte adhérent est maintenant actif.', $crawler->filter('#notice-flashes')->text());
        $this->assertSame('Événements', $crawler->filter('.search-title')->text());

        // Activate user account twice
        $this->logout($this->client);
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertClientIsRedirectedToAuth();
        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Votre compte adhérent est déjà actif.', $crawler->filter('#notice-flashes')->text());

        // Try to authenticate with credentials
        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => 'paul@dupont.tld',
            '_adherent_password' => '#example!12345#',
        ]));

        $this->assertClientIsRedirectedTo('http://'.Config::APP_HOST.'/evenements', $this->client);

        $this->client->followRedirect();
    }

    /**
     * @dataProvider provideSuccessfulMembershipRequests
     */
    public function testCreateMembershipAccountIsSuccessful($country, $city, $cityName, $postalCode, $address)
    {
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['address']['country'] = $country;
        $data['membership_request']['address']['city'] = $city;
        $data['membership_request']['address']['cityName'] = $cityName;
        $data['membership_request']['address']['postalCode'] = $postalCode;
        $data['membership_request']['address']['address'] = $address;

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

    public function provideSuccessfulMembershipRequests()
    {
        return [
            'Foreign' => ['CH', '', 'Zürich', '8057', '36 Zeppelinstrasse'],
            'DOM-TOM Réunion' => ['FR', '97437-97410', '', '97437', '20 Rue Francois Vitry'],
            'DOM-TOM Guadeloupe' => ['FR', '97110-97120', '', '97110', '18 Rue Roby Petreluzzi'],
            'DOM-TOM Polynésie' => ['FR', '98714-98735', '', '98714', '45 Avenue du Maréchal Foch'],
        ];
    }

    public function testLoginAfterCreatingMembershipAccountWithoutConfirmItsEmail()
    {
        // register
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['emailAddress'] = 'michel@dupont.tld';
        $data['membership_request']['address']['country'] = 'CH';
        $data['membership_request']['address']['city'] = '';
        $data['membership_request']['address']['cityName'] = 'Zürich';
        $data['membership_request']['address']['postalCode'] = '8057';
        $data['membership_request']['address']['address'] = '36 Zeppelinstrasse';

        $this->client->submit($this->client->getCrawler()->selectButton('J\'adhère')->form(), $data);

        $this->assertClientIsRedirectedTo('/inscription/don', $this->client);

        $this->authenticateAsAdherent($this->client, $data['membership_request']['emailAddress'], $data['membership_request']['password']['first']);
    }

    public function testDonateWithoutTemporaryDonation()
    {
        $client = $this->client;
        $client->request(Request::METHOD_GET, '/inscription/don');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $client->getResponse());
    }

    public function testDonateWithAFakeValue()
    {
        // register
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['emailAddress'] = 'michel2@dupont.tld';
        $data['membership_request']['address']['country'] = 'CH';
        $data['membership_request']['address']['city'] = '';
        $data['membership_request']['address']['cityName'] = 'Zürich';
        $data['membership_request']['address']['postalCode'] = '8057';
        $data['membership_request']['address']['address'] = '36 Zeppelinstrasse';

        $this->client->submit($this->client->getCrawler()->selectButton('J\'adhère')->form(), $data);

        $this->assertClientIsRedirectedTo('/inscription/don', $this->client);
        $crawler = $this->client->followRedirect();
        $form = $crawler->selectButton('Je soutiens maintenant')->form();
        $this->client->submit($form, ['app_donation[amount]' => 'NaN']);

        $this->assertNotSame(500, $this->client->getResponse()->getStatusCode());
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
        // Set a wrong id
        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, 1234);

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

        $checkBoxPattern = '#app_adherent_pin_interests '.
                           'input[type="checkbox"][name="app_adherent_pin_interests[interests][]"]';

        $this->assertCount(18, $checkboxes = $crawler->filter($checkBoxPattern));

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
        $this->assertEmpty($adherent->getInterests());

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
            $this->assertSame($committees[$i]->getName(), $crawler->filter($boxPattern.' h3')->eq($i)->text());
        }
    }

    public function testChooseNearbyCommitteePersistsMembershipForNonActivatedAdherent()
    {
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');
        $memberships = $this->getCommitteeMembershipRepository()->findMemberships($adherent);
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

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

        $this->assertClientIsRedirectedTo('/inscription/terminee', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertContains('Finalisez dès maintenant votre adhésion', $crawler->text());

        $memberships = $this->getCommitteeMembershipRepository()->findMemberships($adherent);

        $this->assertCount(2, $memberships);
    }

    public function testCannotCreateMembershipAccountRecaptchaConnexionFailure()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['g-recaptcha-response'] = 'connection_failure';
        $crawler = $this->client->submit($crawler->selectButton('J\'adhère')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Une erreur s\'est produite, pouvez-vous réessayer ?', $crawler->filter('#recapture_error')->text());
    }

    private static function createFormData()
    {
        return [
            'g-recaptcha-response' => 'dummy',
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
                'birthdate' => [
                    'year' => '1950',
                    'month' => '1',
                    'day' => '20',
                ],
                'conditions' => true,
                'comMobile' => true,
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadHomeBlockData::class,
        ]);

        $this->adherentRepository = $this->getAdherentRepository();
        $this->activationTokenRepository = $this->getActivationTokenRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;
        $this->activationTokenRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}

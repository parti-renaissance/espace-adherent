<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailjet\Message\AdherentAccountActivationMessage;
use AppBundle\Mailjet\Message\AdherentAccountConfirmationMessage;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Entity\Donation;
use AppBundle\Membership\MembershipUtils;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipControllerTest extends WebTestCase
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
        $crawler = $this->client->submit($crawler->selectButton('become-adherent')->form(), $data);

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
        $crawler = $this->client->submit($crawler->selectButton('become-adherent')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame("Vous devez être âgé d'au moins 15 ans pour adhérer.", $crawler->filter('#field-birthdate > .form__errors > li')->text());
    }

    public function testCannotCreateMembershipAccountIfConditionsAreNotAccepted()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['membership_request']['conditions'] = false;
        $crawler = $this->client->submit($crawler->selectButton('become-adherent')->form(), $data);

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
        $crawler = $this->client->submit($crawler->selectButton('become-adherent')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame("Cette valeur n'est pas un identifiant valide de ville française.", $crawler->filter('#membership-address > .form__errors > li')->text());
    }

    public function testCreateMembershipAccountForFrenchAdherentIsSuccessful()
    {
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('become-adherent')->form(), static::createFormData());

        $this->assertClientIsRedirectedTo('/inscription/don', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertContains(
            "Votre inscription en tant qu'adhérent s'est déroulée avec succès.",
            $crawler->filter('#notice-flashes')->text()
        );
        $this->assertInstanceOf(
            Adherent::class,
            $adherent = $this->client->getContainer()->get('doctrine')->getRepository(Adherent::class)->findByEmail('paul@dupont.tld')
        );

        $this->assertInstanceOf(Adherent::class, $adherent = $this->adherentRepository->findByEmail('paul@dupont.tld'));
        $this->assertInstanceOf(AdherentActivationToken::class, $activationToken = $this->activationTokenRepository->findAdherentMostRecentKey((string) $adherent->getUuid()));
        $this->assertCount(1, $this->emailRepository->findMessages(AdherentAccountActivationMessage::class, 'paul@dupont.tld'));

        // Activate the user account
        $activateAccountUrl = sprintf('/inscription/finaliser/%s/%s', $adherent->getUuid(), $activationToken->getValue());
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertCount(1, $this->emailRepository->findMessages(AdherentAccountConfirmationMessage::class, 'paul@dupont.tld'));

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

        $this->assertInstanceOf(Donation::class, $session->get(MembershipUtils::REGISTERING_DONATION));
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

        $this->client->submit($this->client->getCrawler()->selectButton('become-adherent')->form(), $data);

        $this->assertClientIsRedirectedTo('/inscription/don', $this->client);
        $this->assertInstanceOf(
            Adherent::class,
            $adherent = $this->client->getContainer()->get('doctrine')->getRepository(Adherent::class)->findByEmail('paul@dupont.tld')
        );

        $session = $this->client->getRequest()->getSession();

        $this->assertInstanceOf(Donation::class, $donation = $session->get(MembershipUtils::REGISTERING_DONATION));
        $this->assertSame($adherent->getId(), $session->get(MembershipUtils::NEW_ADHERENT_ID));
        $this->assertSame('Dupont', $donation->getLastName(), 'Temporary donation should be hydrated by the adherent data.');
    }

    public function testDonateWithoutTemporaryDonation()
    {
        $client = $this->client;
        $client->request(Request::METHOD_GET, '/inscription/don');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $client->getResponse());
    }

    public function testPinInterestsWithoutNewAdherentId()
    {
        $this->client->request(Request::METHOD_GET, '/inscription/centre-interets');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testPinInterestsWithWrongNewAdherentId()
    {
        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, 'wrong id');
        $this->client->request(Request::METHOD_GET, '/inscription/centre-interets');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testPinInterests()
    {
        $this->client->getContainer()->get('session')->set(MembershipUtils::NEW_ADHERENT_ID, 1);

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
        /* @var Adherent $adherent */
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

        /* @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');

        $this->assertSame(array_values($chosenInterests), $adherent->getInterests());
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

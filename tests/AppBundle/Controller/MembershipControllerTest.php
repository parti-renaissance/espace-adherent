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
        $data['membership_request']['postalCode'] = '73100';
        $data['membership_request']['city'] = '73100-73999';
        $crawler = $this->client->submit($crawler->selectButton('become-adherent')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame("Cette valeur n'est pas un identifiant valide de ville française.", $crawler->filter('.register__form > form > .form__errors > li')->text());
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
        $data['membership_request']['country'] = 'CH';
        $data['membership_request']['city'] = '';
        $data['membership_request']['postalCode'] = '';
        $data['membership_request']['address'] = '';

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
                'country' => 'FR',
                'postalCode' => '92110',
                'city' => '92110-92024',
                'address' => '92 Bld Victor Hugo',
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

<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailer\Message\AdherentAccountActivationMessage;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\EmailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @var EmailRepository
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
        $data['new_member_ship_request']['emailAddress']['first'] = $emailAddress;
        $data['new_member_ship_request']['emailAddress']['second'] = $emailAddress;
        $crawler = $this->client->submit($crawler->selectButton('Créer mon compte')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $errors = $crawler->filter('.form__error');
        $this->assertSame(1, $errors->count());
        $this->assertSame('Cette adresse e-mail existe déjà.', $errors->text());
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

    public function testCreateMembershipAccountForFrenchAdherentIsSuccessful()
    {
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Créer mon compte')->form(), static::createFormData());

        $this->assertClientIsRedirectedTo('/presque-fini', $this->client);

        $adherent = $this->getAdherentRepository()->findOneByEmail('paul@dupont.tld');
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('Paul', $adherent->getFirstName());
        $this->assertSame('Dupont', $adherent->getLastName());
        $this->assertEmpty($adherent->getAddress());
        $this->assertEmpty($adherent->getCityName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertNull($adherent->getBirthdate());
        $this->assertFalse($adherent->getComMobile());
        $this->assertTrue($adherent->getComEmail());
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());

        $this->assertInstanceOf(
            Adherent::class,
            $adherent = $this->client->getContainer()->get('doctrine')->getRepository(Adherent::class)->findOneByEmail('paul@dupont.tld')
        );

        $this->assertInstanceOf(AdherentActivationToken::class, $activationToken = $this->activationTokenRepository->findAdherentMostRecentKey((string) $adherent->getUuid()));
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(AdherentAccountActivationMessage::class, 'paul@dupont.tld'));

        // Activate the user account
        $activateAccountUrl = sprintf('/inscription/finaliser/%s/%s', $adherent->getUuid(), $activationToken->getValue());
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/adhesion', $this->client);

        $crawler = $this->client->followRedirect();

        // User is automatically logged-in and redirected to the events page
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Votre compte adhérent est maintenant actif.', $crawler->filter('#notice-flashes')->text());

        // Activate user account twice
        $this->logout($this->client);
        $this->client->request(Request::METHOD_GET, $activateAccountUrl);

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Votre compte adhérent est déjà actif.', $crawler->filter('.flash')->text());

        // Try to authenticate with credentials
        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_adherent_email' => 'paul@dupont.tld',
            '_adherent_password' => '#example!12345#',
        ]));

        $this->assertClientIsRedirectedTo('http://'.$this->hosts['app'].'/evenements', $this->client);

        $this->client->followRedirect();
    }

    /**
     * @dataProvider provideSuccessfulMembershipRequests
     */
    public function testCreateMembershipAccountIsSuccessful($country, $postalCode)
    {
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['new_member_ship_request']['address']['country'] = $country;
        $data['new_member_ship_request']['address']['postalCode'] = $postalCode;

        $this->client->submit($this->client->getCrawler()->selectButton('Créer mon compte')->form(), $data);

        $this->assertClientIsRedirectedTo('/presque-fini', $this->client);

        $adherent = $this->getAdherentRepository()->findOneByEmail('paul@dupont.tld');
        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());
    }

    public function provideSuccessfulMembershipRequests()
    {
        return [
            'Foreign' => ['CH', '8057'],
            'DOM-TOM Réunion' => ['FR', '97437'],
            'DOM-TOM Guadeloupe' => ['FR', '97110'],
            'DOM-TOM Polynésie' => ['FR', '98714'],
        ];
    }

    public function testLoginAfterCreatingMembershipAccountWithoutConfirmItsEmail()
    {
        // register
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['new_member_ship_request']['emailAddress']['first'] = 'michel@dupont.tld';
        $data['new_member_ship_request']['emailAddress']['second'] = 'michel@dupont.tld';
        $data['new_member_ship_request']['address']['country'] = 'CH';
        $data['new_member_ship_request']['address']['postalCode'] = '8057';

        $this->client->submit($this->client->getCrawler()->selectButton('Créer mon compte')->form(), $data);

        $this->assertClientIsRedirectedTo('/presque-fini', $this->client);

        // login
        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_adherent_email' => $data['new_member_ship_request']['emailAddress']['first'],
            '_adherent_password' => $data['new_member_ship_request']['password'],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/evenements', $this->client, true);
    }

    public function testDonateWithAFakeValue()
    {
        // register
        $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['new_member_ship_request']['emailAddress']['first'] = 'michel2@dupont.tld';
        $data['new_member_ship_request']['emailAddress']['second'] = 'michel2@dupont.tld';
        $data['new_member_ship_request']['address']['country'] = 'CH';
        $data['new_member_ship_request']['address']['postalCode'] = '8057';

        $this->client->submit($this->client->getCrawler()->selectButton('Créer mon compte')->form(), $data);

        $this->assertClientIsRedirectedTo('/presque-fini', $this->client);
    }

    public function testCannotCreateMembershipAccountRecaptchaConnectionFailure()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['g-recaptcha-response'] = 'connection_failure';
        $crawler = $this->client->submit($crawler->selectButton('Créer mon compte')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $errors = $crawler->filter('.flash-error_recaptcha');
        $this->assertSame(1, $errors->count());
        $this->assertSame('Une erreur s\'est produite, pouvez-vous réessayer ?', $errors->text());
    }

    private static function createFormData()
    {
        return [
            'g-recaptcha-response' => 'dummy',
            'new_member_ship_request' => [
                'firstName' => 'Paul',
                'lastName' => 'Dupont',
                'emailAddress' => [
                    'first' => 'paul@dupont.tld',
                    'second' => 'paul@dupont.tld',
                ],
                'password' => '#example!12345#',
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '92110',
                ],
                'comEmail' => true,
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
        $this->emailRepository = $this->getEmailRepository();
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
